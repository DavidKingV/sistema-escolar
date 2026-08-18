import {
  initializeGroupsDataTable,
  initializeDuplicatesDataTable,
  initializeGroupsStudentsDataTable,
} from "../datatables/index.js";
import { FillTable, CleanInputsGroupsEdit, FillDivsGroups } from "./forms.js";
import {
  validateForm,
  capitalizeAllWords,
  capitalizeAll,
} from "../global/validate/index.js";
import {
  errorAlert,
  successAlert,
  infoAlert,
  loadingSpinner,
  confirmAlert,
} from "../utils/alerts.js";

initializeGroupsDataTable();

const showLoader = () => $("#globalLoader").fadeIn(200);

$("#groupsTable").on("click", ".editGroup", async function () {
  let groupId = $(this).data("id");
  showLoader();
  if (groupId) {
    await $.post(
      "modals/GroupsEditModal.php",
      { groupId: groupId },
      function (data) {
        $("#modalBodyEditGroup").html(data);
      },
    );
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "No se ha podido obtener el ID del grupo",
    });
  }
});

// index.js - Solo lógica de update
function handleUpdateGroup(groupDataEdit) {
  Swal.fire({
    title: "¿Estás seguro de actualizar al grupo?",
    text: "Esta acción no se puede deshacer",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51)",
    confirmButtonText: "Sí, actualizar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      UpdateGroup(groupDataEdit);
    }
  });
}

$("#groupsTable").on("click", ".deleteGroup", function () {
  let groupId = $(this).data("id");
  if (groupId) {
    Swal.fire({
      title: "¿Estás seguro de eliminar al grupo?",
      text: "Esta acción no se puede deshacer",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(48, 133, 214)",
      cancelButtonColor: "rgb(221, 51, 51);",
      confirmButtonText: "Sí, eliminar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        DeleteGroup(groupId);
      }
    });
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "No se ha podido obtener el ID del grupo",
    });
  }
});

$("#groupsTable").on("click", ".groupDetails", function () {
  let groupId = $(this).data("id");
  if (groupId) {
    window.location.href = "grupos/detalles.php?id=" + groupId;
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "No se ha podido obtener el ID del grupo",
    });
  }
});

$("#groupsTable").on("click", ".groupSchedules", function () {
  let groupId = $(this).data("id");
  if (groupId) {
    window.location.href = "grupos/horarios.php?id=" + groupId;
  } else {
    Swal.fire({
      icon: "error",
      title: "Oops...",
      text: "No se ha podido obtener el ID del grupo",
    });
  }
});

$(function () {
  let currentPath = window.location.pathname;

  if (currentPath.endsWith("/grupos/detalles.php")) {
    //GetStudentsNames();
    const urlParams = new URLSearchParams(window.location.search);
    const groupId = urlParams.get("id");

    if (groupId) {
      GetDataGroupDetails(groupId);
      initializeGroupsStudentsDataTable(groupId);
    } else {
      Swal.fire({
        icon: "error",
        title: "Oops...",
        text: "No se ha podido obtener el ID del grupo",
      });
    }
  }
});

const GetDataGroupDetails = async (groupId) => {
  try {
    const response = await $.ajax({
      url: "../../backend/groups/routes.php",
      type: "GET",
      data: { groupId: groupId, action: "getGroupData" },
    });
    if (!response.success) {
      throw new Error(response.message);
    } else {
      FillDivsGroups(response);
    }
  } catch (error) {
    console.error("Error: ", error);
    Swal.fire({
      icon: "error",
      title: "Error al obtener los datos del grupo",
      text: error.message,
    });
  }
};

$("#addStudentGroupForm").on("submit", function (e) {
  e.preventDefault();
  let groupIdUrl = new URLSearchParams(window.location.search);
  let groupId = groupIdUrl.get("id");
  let studentId = $("#studentIdGroup").val();

  Swal.fire({
    title: "¿Estás seguro de agregar al alumno al grupo?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51)",
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      AddStudentGroup(groupId, studentId);
    }
  });
});

$("#groupStudentsTable").on("click", ".deleteGroupStudent", async function () {
  let studentId = $(this).data("id");
  let groupId = $(this).data("group");

  const result = await Swal.fire({
    title: "¿Estás seguro de eliminar al alumno del grupo?",
    text: "Ingresa tu contraseña para continuar",
    icon: "warning",
    input: "password",
    inputPlaceholder: "Contraseña",
    inputAttributes: {
      autocapitalize: "off",
      autocorrect: "off",
    },
    showCancelButton: true,
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "#d33",
    allowOutsideClick: false,
    inputValidator: (value) => {
      if (!value) {
        return "Debes ingresar tu contraseña";
      }
    },
  });

  if (result.isConfirmed) {
    const password = result.value;

    DeleteStudentGroup(groupId, studentId, password);
  }
});

const DeleteStudentGroup = async (groupId, studentId, password) => {
  try {
    const response = await $.ajax({
      url: "../../backend/groups/routes.php",
      type: "POST",
      data: {
        groupId: groupId,
        studentId: studentId,
        password: password,
        action: "deleteStudentGroup",
      },
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Alumno eliminado",
        text: response.message,
      });
      // Reload the table
      $("#groupStudentsTable").DataTable().ajax.reload();
      GetStudentsNames();
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al eliminar el alumno",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al eliminar el alumno",
      text: error.message,
    });
  }
};

const AddStudentGroup = async (groupId, studentId) => {
  try {
    const response = await $.ajax({
      url: "../../backend/groups/routes.php",
      type: "POST",
      data: {
        groupId: groupId,
        studentId: studentId,
        action: "addStudentGroup",
      },
      dataType: "json",
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Alumno agregado",
        text: response.message,
      });
      // Reload the table
      $("#groupStudentsTable").DataTable().ajax.reload();
      $("#addStudentGroup").validate().resetForm();
      $("#studentIdGroup").val(null).trigger("change");
      await GetStudentsNames();
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al agregar el alumno",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al agregar el alumno",
      text: error.message,
    });
  }
};

const DeleteGroup = async (groupId) => {
  try {
    const response = await $.ajax({
      url: "../backend/groups/routes.php",
      type: "POST",
      data: { groupId: groupId, action: "deleteGroup" },
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Grupo eliminado",
        text: response.message,
      });
      // Reload the table
      $("#groupsTable").DataTable().ajax.reload();
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al eliminar el grupo",
        text: response.message,
      });
    }
  } catch (error) {
    console.error("Error: ", error);
    Swal.fire({
      icon: "error",
      title: "Error al eliminar el grupo",
      text: error.message,
    });
  }
};

const UpdateGroup = async (groupDataEdit) => {
  try {
    const response = await $.ajax({
      url: "../backend/groups/routes.php",
      type: "POST",
      data: { groupDataEdit: groupDataEdit, action: "updateGroup" },
    });
    if (response.success) {
      // Show a success message
      Swal.fire({
        icon: "success",
        title: "Grupo actualizado",
        text: response.message,
      });
      // Reload the table
      $("#groupsTable").DataTable().ajax.reload();
      $("#GroupsEditModal").modal("hide");
    } else {
      // Show an error message
      Swal.fire({
        icon: "error",
        title: "Error al actualizar el grupo",
        text: response.message,
      });
    }
  } catch (error) {
    console.error("Error: ", error);
    Swal.fire({
      icon: "error",
      title: "Error al actualizar el grupo",
      text: error.message,
    });
  }
};

const GetStudentsNames = async () => {
  const GetStudentsSelect = async () => {
    try {
      const response = await $.ajax({
        url: "../../../backend/groups/routes.php",
        type: "GET",
        data: { action: "getStudentsNames" },
        dataType: "json",
      });
      return response;
    } catch (error) {
      console.error("Error al obtener los datos:", error);
      throw new Error("Error al obtener los datos");
    }
  };

  try {
    const students = await GetStudentsSelect();

    if (!students || students.length === 0) {
      console.log("No se encontraron alumnos");
      return;
    }

    let $select = $("#studentIdGroup");

    // ← Destruir Select2 y limpiar opciones antes de repoblar
    if ($select.hasClass("select2-hidden-accessible")) {
      $select.select2("destroy");
    }
    $select.empty();

    $.each(students, function (index, student) {
      if (student.success !== false) {
        $select.append(
          $("<option>", {
            value: student.id,
            text: student.name,
          }),
        );
      }
    });

    $select.select2({
      theme: "bootstrap-5",
      placeholder: "Selecciona uno o varios alumnos",
    });
  } catch (error) {
    console.error("Error al procesar los datos:", error.message);
  }
};

// const GetDataGroupEdit = async (groupId) => {
//   try {
//     // Función para obtener el valor predeterminado de la base de datos usando async/await
//     const getDefaultCareer = async () => {
//       const response = await $.ajax({
//         url: "../backend/groups/routes.php",
//         type: "GET",
//         data: { groupId: groupId, action: "getGroupData" },
//       });
//       if (!response.success) {
//         throw new Error(response.message);
//       } else {
//         FillTable(response);
//         return response.carreer_name;
//       }
//     };

//     // Función para cargar el JSON de carreras
//     const loadCareers = async () => {
//       const response = await $.ajax({
//         url: "../backend/groups/routes.php",
//         type: "GET",
//         data: { action: "getGroupsJson" },
//       });
//       if (!response) {
//         throw new Error(response.message);
//       } else {
//         return response;
//       }
//     };

//     // Obtener el valor predeterminado
//     const defaultCareer = await getDefaultCareer();

//     // Cargar el JSON de carreras
//     const careers = await loadCareers();

//     let $selectEdit = $("#carreerNameGroupEdit");
//     $.each(careers, function (area, subareas) {
//       let $mainOptgroup = $("<optgroup>", { label: area.replace(/_/g, " ") });
//       $.each(subareas, function (subarea, programs) {
//         let $subOptgroup = $("<optgroup>", {
//           label: "  " + subarea.replace(/_/g, " "),
//         }); // Agrega espacios para simular jerarquía
//         $.each(programs, function (index, program) {
//           let $option = $("<option>", {
//             value: program.id,
//             text: "    " + program.nombre,
//           });
//           // Verificar si esta opción coincide con el valor predeterminado
//           if (program.nombre === defaultCareer) {
//             $option.prop("selected", true); // Establecer la opción como seleccionada
//           }

//           $subOptgroup.append($option); // Agrega la opción al subgrupo
//         });
//         $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
//       });

//       $selectEdit.append($mainOptgroup);
//     });

//     // Inicializar Select2
//     $selectEdit.select2({
//       theme: "bootstrap-5",
//       dropdownParent: $("#GroupsEditModal"),
//     });
//   } catch (error) {
//     console.error("Error: ", error);
//   }
// };

const GetCarreerName = async () => {
  try {
    // Función para obtener el valor predeterminado de la base de datos usando async/await

    // Función para cargar el JSON de carreras
    const loadCareers = async () => {
      const response = await $.ajax({
        url: "../../backend/groups/routes.php",
        type: "GET",
        data: { action: "getGroupsJson" },
      });
      if (!response) {
        throw new Error(response.message);
      } else {
        return response;
      }
    };

    // Cargar el JSON de carreras
    const careers = await loadCareers();

    let $select = $("#carreerNameGroup");
    $.each(careers, function (area, subareas) {
      let $mainOptgroup = $("<optgroup>", { label: area.replace(/_/g, " ") });

      $.each(subareas, function (subarea, programs) {
        let $subOptgroup = $("<optgroup>", {
          label: "  " + subarea.replace(/_/g, " "),
        }); // Agrega espacios para simular jerarquía
        $.each(programs, function (index, program) {
          let $option = $("<option>", {
            value: program.id,
            text: "    " + program.nombre,
          });

          $subOptgroup.append($option); // Agrega la opción al subgrupo
        });
        $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
      });

      $select.append($mainOptgroup);
    });

    // Inicializar Select2
    $select.select2({
      theme: "bootstrap-5",
    });
  } catch (error) {
    console.error("Error: ", error);
  }
};

GetCarreerName();

// =======================
// Cargar grupos del alumno en el modal
// =======================
const loadStudentDuplicateGroups = async (studentId) => {
  try {
    const response = await $.ajax({
      url: "../../backend/groups/routes.php",
      type: "POST",
      data: { action: "getStudentDuplicateGroups", studentId },
      dataType: "json",
    });

    if (!response.success) {
      throw new Error(response.message);
    }

    const $list = $("#duplicateGroupsList");
    $list.empty();

    response.results.forEach((group) => {
      $list.append(`
                <div class="form-check card p-3 mb-2 border">
                    <div class="d-flex align-items-start gap-3">
                        <input class="form-check-input mt-1" type="radio" 
                               name="correctGroup" 
                               id="group_${group.group_id}" 
                               value="${group.group_id}">
                        <label class="form-check-label w-100" for="group_${group.group_id}">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong>${group.group_nombre}</strong>
                                <span class="badge bg-primary">${group.clave}</span>
                            </div>
                            <div class="text-muted mt-1" style="font-size: 13px;">
                                <span><i class="bi bi-book"></i> ${group.carreer_nombre}</span>
                                <span class="ms-3"><i class="bi bi-tag"></i> ${group.subarea}</span>
                                <span class="ms-3"><i class="bi bi-calendar"></i> Asignado: ${group.assigned_at}</span>
                            </div>
                        </label>
                    </div>
                </div>
            `);
    });

    // Habilitar botón al seleccionar un radio
    $('input[name="correctGroup"]').on("change", function () {
      $("#btnSaveDuplicate").prop("disabled", false);
    });
  } catch (error) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
  }
};

// =======================
// Resolver duplicado
// =======================
const resolveDuplicate = async (studentId) => {
  const correctGroupId = $('input[name="correctGroup"]:checked').val();

  if (!correctGroupId) {
    Swal.fire({
      icon: "warning",
      title: "Selecciona un grupo",
      text: "Debes seleccionar el grupo correcto.",
    });
    return;
  }

  try {
    const response = await $.ajax({
      url: "../../backend/groups/routes.php",
      type: "POST",
      data: { action: "resolveDuplicate", studentId, correctGroupId },
      dataType: "json",
    });

    if (!response.success) {
      throw new Error(response.message);
    }

    Swal.fire({ icon: "success", title: "Resuelto", text: response.message });

    // Cerrar modal y recargar tabla
    $("#duplicatesModal").modal("hide");
    $("#duplicatesTable").DataTable().ajax.reload();
  } catch (error) {
    Swal.fire({ icon: "error", title: "Error", text: error.message });
  }
};

initializeDuplicatesDataTable();

// Abrir modal y cargar grupos del alumno
$("#duplicatesTable").on("click", ".btnResolveDuplicate", async function () {
  const studentId = $(this).data("student-id");
  const studentName = $(this).data("student-name");

  $("#duplicateStudentName").text(studentName);
  $("#btnSaveDuplicate").prop("disabled", true).data("student-id", studentId);
  $("#duplicateGroupsList").html(`
            <div class="text-center py-3">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
            </div>
        `);

  $("#duplicatesModal").modal("show");
  await loadStudentDuplicateGroups(studentId);
});

// Confirmar resolución
$("#btnSaveDuplicate").on("click", async function () {
  const studentId = $(this).data("student-id");

  Swal.fire({
    title: "¿Confirmar cambio?",
    text: "Se eliminarán los grupos incorrectos del alumno. Esta acción no se puede deshacer.",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51)",
    confirmButtonText: "Sí, confirmar",
    cancelButtonText: "Cancelar",
  }).then(async (result) => {
    if (result.isConfirmed) {
      await resolveDuplicate(studentId);
    }
  });
});

// Refrescar tabla
$("#btnRefreshDuplicates").on("click", function () {
  $("#duplicatesTable").DataTable().ajax.reload();
});

//miselanious

$("#GroupsEditModal").on("hidden.bs.modal", function () {
  CleanInputsGroupsEdit();
});

// =======================
// Snapshot para grupos
// =======================
let originalGroupData = {};

const normalizeValue = (val) => (val ?? "").toString().trim();

const getCurrentGroupFormData = () => ({
  idGroupDB: normalizeValue($("#idGroupDB").val()),
  carreerNameGroupEdit: normalizeValue($("#carreerNameGroupEdit").val()),
  keyGroupEdit: normalizeValue($("#keyGroupEdit").val()),
  nameGroupEdit: normalizeValue($("#nameGroupEdit").val()),
  startDateEdit: normalizeValue($("#startDateEdit").val()),
  endDateEdit: normalizeValue($("#endDateEdit").val()),
  descriptionGroupEdit: normalizeValue($("#descriptionGroupEdit").val()),
});

const hasGroupChanges = () => {
  const currentData = getCurrentGroupFormData();
  return Object.keys(originalGroupData).some(
    (key) => currentData[key] !== originalGroupData[key],
  );
};

const observeGroupChanges = () => {
  $("#updateGroup")
    .find("input, select")
    .off("input change")
    .on("input change", () => {
      setTimeout(() => {
        const changed = hasGroupChanges();
        $("#saveGroupChanges").prop("disabled", !changed);
      }, 0);
    });
};

// Llama esto después de llenar el form con los datos del servidor
const setGroupSnapshot = () => {
  originalGroupData = getCurrentGroupFormData();
  $("#saveGroupChanges").prop("disabled", true); // Inicia deshabilitado
};

export { handleUpdateGroup, observeGroupChanges, setGroupSnapshot };
