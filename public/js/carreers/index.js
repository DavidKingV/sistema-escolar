import { initializeCarreersDataTable } from "../datatables/index.js";
import { FillTable, ClearInputsEditTeachers } from "../carreers/forms.js";
import {
  errorAlert,
  successAlert,
  infoAlert,
  loadingSpinner,
  confirmAlert,
} from "../utils/alerts.js";
import {
  validateForm,
  capitalizeAllWords,
  capitalizeAll,
} from "../global/validate/index.js";
import { createFormObserver, serializeForm } from "../utils/formChanges.js";
import {
  confirmSensitiveAction,
  handleSensitiveActionResponse,
} from "../utils/sensitiveActions.js";

const careerObserver = createFormObserver({
  form: "#updateCareer",
  saveButton: "#saveCareerChanges",
  getCurrentData: () => serializeForm("#updateCareer"),
});

careerObserver.observe();

initializeCarreersDataTable();

$(function () {
  let currentPath = window.location.pathname;

  if (currentPath.endsWith("/carreras/altas.php")) {
    $.getJSON("../../backend/areas.json", function (carreras) {
      let $select = $("#careerName");
      $.each(carreras, function (area, subareas) {
        let $mainOptgroup = $("<optgroup>", { label: area.replace(/_/g, " ") });

        $.each(subareas, function (subarea, programas) {
          let $subOptgroup = $("<optgroup>", {
            label: "  " + subarea.replace(/_/g, " "),
          }); // Agrega espacios para simular jerarquía
          $.each(programas, function (index, programa) {
            $subOptgroup.append(
              $("<option>", {
                text: "    " + programa,
                value: programa,
                "data-area": area.replace(/_/g, " "), // Guarda el área en un atributo de datos
                "data-subarea": subarea.replace(/_/g, " "), // Guarda el subárea en un atributo de datos
              }),
            ); // Agrega espacios para simular jerarquía
          });
          $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
        });

        $select.append($mainOptgroup);
      });

      $select.select2({
        theme: "bootstrap-5",
      });

      // Controlador de eventos change para actualizar los campos de entrada
      $select.on("change", function () {
        let selectedOption = $(this).find("option:selected");
        $("#careerArea").val(selectedOption.data("area"));
        $("#careerSubarea").val(selectedOption.data("subarea"));
      });
    });
  }

  validateForm(
    "#addCarreers",
    {
      careerName: {
        required: true,
        valueNotEquals: "0",
      },
      careerArea: {
        required: true,
      },
      careerSubarea: {
        required: true,
      },
      careerDes: {
        required: true,
        minlength: 1,
      },
    },
    {
      careerName: {
        required: "Por favor selecciona una carrera",
        valueNotEquals: "Por favor selecciona una carrera",
      },
      careerArea: {
        required: "El área de la carrera es obligatoria",
      },
      careerSubarea: {
        required: "El subárea de la carrera es obligatoria",
      },
      careerDes: {
        required: "La descripción de la carrera es obligatoria",
        minlength: "La descripción debe tener al menos 1 carácter",
      },
    },
  );
});

$("#carreersTable").on("click", ".editCarreer", function (e) {
  e.preventDefault();
  let carreerId = $(this).data("id");

  // Abre loader
  $("#careerEditLoader").css("display", "flex");

  if (carreerId) {
    GetCarreerData(carreerId);
  } else {
    Swal.fire({
      icon: "error",
      title: "ID del alumno no proporcionado",
      text: "Por favor proporciona un ID válido para editar.",
    });
  }
});

$("#addCarreers").on("submit", function (event) {
  event.preventDefault();
  const formData = $(this).serializeArray();

  if ($(this).valid()) {
    Swal.fire({
      title: "¿Estás seguro de agregar la carrera?",
      text: "Esta acción no se puede deshacer",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(48, 133, 214)",
      cancelButtonColor: "rgb(221, 51, 51);",
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        if ($(this).valid()) {
          AddCareer(formData);
        } else {
          Swal.fire({
            icon: "error",
            title: "Error en la validación",
            text: "Por favor, verifica que todos los campos estén llenos y sean correctos.",
          });
        }
      }
    });
  } else {
    infoAlert(
      "Por favor, verifica que todos los campos estén llenos y sean correctos.",
    );
  }
});

$("#updateCareer").on("submit", function (event) {
  event.preventDefault();
  let formData = $(this).serialize();
  Swal.fire({
    title: "¿Estás seguro de actualizar la carrera?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51);",
    confirmButtonText: "Sí, actualizar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      UpdateCarreer(formData);
    }
  });
});

$("#carreersTable").on("click", ".deleteCarreerById", async function () {
  let carreerId = $(this).data("id");
  const result = await confirmSensitiveAction({
    title: "¿Estás seguro de eliminar la carrera?",
    confirmButtonText: "Sí, eliminar",
  });

  if (result.isConfirmed) {
    DeleteCarreer(carreerId, result.password);
  }
});

const DeleteCarreer = async (carreerId, password) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/carreer/deleteCarreerById`,
      type: "POST",
      data: {
        carreerId: carreerId,
        password: password,
      },
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Carrera eliminada",
        text: response.message,
      });
      // Reload the table
      $("#carreersTable").DataTable().ajax.reload();
    } else if (!(await handleSensitiveActionResponse(response))) {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al eliminar la carrera",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al eliminar la carrera",
      text: "Ocurrió un error al eliminar la carrera, por favor intenta de nuevo más tarde.",
    });
  }
};

const UpdateCarreer = async (carreerUpdateData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/carreer/updateCarreer`,
      type: "POST",
      data: carreerUpdateData,
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Carrera actualizada",
        text: response.message,
      });
      // Reload the table
      $("#carreersTable").DataTable().ajax.reload();
      $("#CareerEditModal").modal("hide");
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al actualizar la carrera",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al actualizar la carrera",
      text: "Ocurrió un error al actualizar la carrera, por favor intenta de nuevo más tarde.",
    });
  }
};

const GetCarreerData = async (carreerId) => {
  try {
    // Función para obtener el valor predeterminado de la base de datos usando async/await
    const getDefaultCareer = async () => {
      const response = await $.ajax({
        url: `${BASE_URL}/carreer/getCarreerById`,
        type: "GET",
        data: { carreerId: carreerId },
      });
      if (!response.success) {
        throw new Error(response.message);
      } else {
        FillTable(response.data);

        careerObserver.saveSnapshot();
        return response.data.name;
      }
    };

    // Función para cargar el JSON de carreras
    const loadCarreers = async () => {
      const response = await $.getJSON("../backend/areas.json");
      return response;
    };

    // Obtener el valor predeterminado
    const defaultCareer = await getDefaultCareer();

    // Cargar el JSON de carreras
    const carreers = await loadCarreers();

    let $selectEdit = $("#careerNameEdit");
    $.each(carreers, function (area, subareas) {
      let $mainOptgroup = $("<optgroup>", { label: area.replace(/_/g, " ") });

      $.each(subareas, function (subarea, programs) {
        let $subOptgroup = $("<optgroup>", {
          label: "  " + subarea.replace(/_/g, " "),
        }); // Agrega espacios para simular jerarquía
        $.each(programs, function (index, program) {
          let $option = $("<option>", {
            value: program,
            text: "    " + program,
            "data-area": area.replace(/_/g, " "), // Guarda el área en un atributo de datos
            "data-subarea": subarea.replace(/_/g, " "), // Guarda el subárea en un atributo de datos
          });

          // Verificar si esta opción coincide con el valor predeterminado
          if (program === defaultCareer) {
            $option.prop("selected", true); // Establecer la opción como seleccionada
          }

          $subOptgroup.append($option); // Agrega la opción al subgrupo
        });
        $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
      });

      $selectEdit.append($mainOptgroup);
    });

    // Inicializar Select2
    $selectEdit.select2({
      theme: "bootstrap-5",
      dropdownParent: $("#CareerEditModal"),
      bindEvents: false,
    });

    const updateInputFields = () => {
      let selectedOption = $selectEdit.find("option:selected");
      $("#carreerAreaEdit").val(selectedOption.data("area"));
      $("#careerSubareaEdit").val(selectedOption.data("subarea"));
    };

    // Llamar a la función para actualizar los campos de entrada inicialmente
    updateInputFields();

    $selectEdit.on("change", function () {
      let selectedOption = $(this).find("option:selected");
      $("#carreerAreaEdit").val(selectedOption.data("area"));
      $("#careerSubareaEdit").val(selectedOption.data("subarea"));
    });
  } catch (error) {
    console.error("Error: ", error);
  } finally {
    // Cierra loader
    $("#careerEditLoader").hide();
  }
};

const AddCareer = async (carreerData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/carreer/addCarreer`,
      type: "POST",
      data: carreerData,
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Carrera agregada",
        text: response.message,
      });
      // Reload the table
      $("#addCarreers")[0].reset();
      $("#careerName").val(0).trigger("change");
      $("#careerArea").val("");
      $("#careerSubarea").val("");
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al agregar la carrera",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al agregar la carrera",
      text: "Ocurrió un error al agregar la carrera, por favor intenta de nuevo más tarde.",
    });
  }
};

//miselanius

$("#CareerEditModal").on("hidden.bs.modal", function () {
  ClearInputsEditTeachers();
});
