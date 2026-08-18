import {
  FillTable,
  FillChildInfo,
  ClearSubjectChildInputs,
} from "../subjects/forms.js";
import { initializeSubjectsDataTable } from "../datatables/index.js";
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

const subjectObserver = createFormObserver({
  form: "#updateSubject",
  saveButton: "#saveSubjectChanges",
  getCurrentData: () => serializeForm("#updateSubject"),
});

subjectObserver.observe();

const childSubjectObserver = createFormObserver({
  form: "#subjectChildInfo",
  saveButton: "#updateSubjectChild",
  getCurrentData: () => serializeForm("#subjectChildInfo"),
});

childSubjectObserver.observe();

initializeSubjectsDataTable();

$("#subjectsTable").on("click", ".editSubject", function () {
  let subjectId = $(this).data("id");

  // Abre loader
  $("#subjectEditLoader").css("display", "flex");

  if (subjectId) {
    GetSubjectData(subjectId);
  } else {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo obtener el ID de la materia, por favor intenta de nuevo.",
    });
  }
});

$("#updateSubjectChild").click(function () {
  let formData = $("#subjectChildInfo").serialize();

  Swal.fire({
    title: "¿Estás seguro de actualizar los datos de la materia hija?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51);",
    confirmButtonText: "Sí, actualizar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      if ($(this).valid()) {
        UpdateSubjectChild(formData);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error en la validación",
          text: "Por favor, verifica que todos los campos estén llenos y sean correctos.",
        });
      }
    }
  });
});

$("#deleteSubjectChildById").on("click", async function () {
  let subjectChildId = $("#idMainSubjectInfo").val();

  // Obtener instancia del modal de Bootstrap y ocultarlo
  const modalEl = document.getElementById("childSubjectsModal");
  const modal = bootstrap.Modal.getInstance(modalEl);

  modal.hide();

  // Esperar a que el modal termine de cerrarse antes de abrir Swal
  await new Promise((resolve) => $(modalEl).one("hidden.bs.modal", resolve));

  const result = await Swal.fire({
    title: "¿Estás seguro de eliminar la materia hija?",
    text: "Ingresa tu contraseña para continuar",
    icon: "warning",
    input: "password",
    inputPlaceholder: "Contraseña",
    inputAttributes: {
      autocapitalize: "off",
      autocorrect: "off",
    },
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
    inputValidator: (value) => {
      if (!value) {
        return "Debes ingresar tu contraseña";
      }
    },
  });

  // Si cancela, reabrir el modal
  if (!result.isConfirmed) {
    modal.show();
    return;
  }

  const password = result.value;
  DeleteSubjectChild(subjectChildId, password);
});

$("#subjectsTable").on("click", ".subjectChildInfo", async function () {
  // Abre loader
  $("#subjectChildEditLoader").css("display", "flex");
  let subjectFatherId = $(this).data("idfather");
  let subjectChildId = $(this).data("idchild");
  if (subjectFatherId && subjectChildId) {
    await GetChildSubjectsData(subjectFatherId, subjectChildId);
  } else {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo obtener el ID de la materia, por favor intenta de nuevo.",
    });
  }
});

$("#subjectsTable").on("click", ".addChildSubject", function () {
  let subjectId = $(this).data("id");
  let subjectName = $(this).data("name");
  let carrerId = $(this).data("carrerid");

  if (subjectId) {
    $("#idMainSubject").val(subjectId);
    $("#subjectManinName").val(subjectName);
    $("#carrerId").val(carrerId);
  } else {
    Swal.fire({
      icon: "error",
      title: "Error",
      text: "No se pudo obtener el ID de la materia, por favor intenta de nuevo.",
    });
  }
});

$("#addSubjectChild").submit(function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  Swal.fire({
    title: "¿Estás seguro de agregar la materia hija?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51);",
    confirmButtonText: "Sí, agregar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      if ($(this).valid()) {
        AddSubjectChild(formData);
      } else {
        Swal.fire({
          icon: "error",
          title: "Error en la validación",
          text: "Por favor, verifica que todos los campos estén llenos y sean correctos.",
        });
      }
    }
  });
});

$("#updateSubject").submit(function (e) {
  e.preventDefault();
  let formData = $(this).serialize();
  Swal.fire({
    title: "¿Estás seguro de actualizar los datos de la materia?",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "rgb(48, 133, 214)",
    cancelButtonColor: "rgb(221, 51, 51);",
    confirmButtonText: "Sí, actualizar",
    cancelButtonText: "Cancelar",
  }).then((result) => {
    if (result.isConfirmed) {
      if ($(this).valid()) {
        UpdateSubjectData(formData);
        $("#SubjectsEditModal").modal("hide");
      } else {
        Swal.fire({
          icon: "error",
          title: "Error en la validación",
          text: "Por favor, verifica que todos los campos estén llenos y sean correctos.",
        });
      }
    }
  });
});

$("#subjectsTable").on("click", ".deleteSubjectById", function () {
  let subjectId = $(this).data("id");
  Swal.fire({
    title: "¿Estás seguro de eliminar la materia?",
    text: "Ingresa tu contraseña para continuar",
    icon: "warning",
    input: "password",
    inputPlaceholder: "Contraseña",
    inputAttributes: {
      autocapitalize: "off",
      autocorrect: "off",
    },
    showCancelButton: true,
    confirmButtonColor: "#d33",
    confirmButtonText: "Sí, eliminar",
    cancelButtonText: "Cancelar",
    allowOutsideClick: false,
    inputValidator: (value) => {
      if (!value) {
        return "Debes ingresar tu contraseña";
      }
    },
  }).then((result) => {
    if (result.isConfirmed) {
      const password = result.value;
      DeleteSubject(subjectId, password);
    }
  });
});

validateForm(
  "#addSubjects",
  {
    subjectKey: {
      required: true,
      minlength: 2,
      maxlength: 10,
    },
    subjectName: {
      required: true,
      minlength: 2,
      maxlength: 100,
    },
    subjectDes: {
      maxlength: 250,
    },
  },
  {
    subjectKey: {
      required: "La clave de la materia es obligatoria.",
      minlength: "La clave de la materia debe tener al menos 2 caracteres.",
      maxlength: "La clave de la materia no debe exceder los 10 caracteres.",
    },
    subjectName: {
      required: "El nombre de la materia es obligatorio.",
      minlength: "El nombre de la materia debe tener al menos 2 caracteres.",
      maxlength: "El nombre de la materia no debe exceder los 100 caracteres.",
    },
    subjectDes: {
      maxlength: "Los comentarios no deben exceder los 250 caracteres.",
    },
  },
);

$("#addSubjects").submit(function (e) {
  e.preventDefault();
  let formData = $(this).serialize();

  if ($(this).valid()) {
    Swal.fire({
      title: "¿Estás seguro de agregar la materia?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "rgb(48, 133, 214)",
      cancelButtonColor: "rgb(221, 51, 51);",
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
    }).then((result) => {
      if (result.isConfirmed) {
        if ($(this).valid()) {
          AddSubject(formData);
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
      "Error en la validación.Por favor, verifica que todos los campos estén llenos y sean correctos.",
    );
  }
});

const AddSubject = async (subjectData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/addSubject`,
      type: "POST",
      data: subjectData,
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Materia agregada",
        text: response.message,
      }).then(() => {
        $("#addSubjects")[0].reset();
        $("#subjectsTable").DataTable().ajax.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al agregar la materia",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al agregar la materia",
      text: "Ocurrió un error al agregar la materia, por favor intenta de nuevo más tarde.",
    });
  }
};

const DeleteSubject = async (subjectId, password) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/deleteSubjectById`,
      type: "POST",
      data: {
        subjectId: subjectId,
        password: password,
      },
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Materia eliminada",
        text: response.message,
      }).then(() => {
        $("#subjectsTable").DataTable().ajax.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al eliminar la materia",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al eliminar la materia",
      text: "Ocurrió un error al eliminar la materia, por favor intenta de nuevo más tarde.",
    });
  }
};

const UpdateSubjectData = async (subjectUpdateData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/updateSubject`,
      type: "POST",
      data: subjectUpdateData,
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Datos actualizados",
        text: response.message,
      }).then(() => {
        $("#subjectsTable").DataTable().ajax.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al actualizar los datos",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al actualizar los datos de la materia",
      text: "Ocurrió un error al actualizar los datos de la materia, por favor intenta de nuevo más tarde.",
    });
  }
};

const GetSubjectData = async (subjectId) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/getSubjectById`,
      type: "GET",
      data: { subjectId: subjectId },
    });
    if (response.success) {
      FillTable(response.data);

      subjectObserver.saveSnapshot();
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al obtener los datos",
        text: response.message,
        confirmButtonText: "Iniciar sesión",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "index.html";
        }
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al obtener los datos de la materia",
      text: "Ocurrió un error al obtener los datos de la materia, por favor intenta de nuevo más tarde.",
    });
  } finally {
    // Cierra loader
    $("#subjectEditLoader").hide();
  }
};

const UpdateSubjectChild = async (subjectChildUpdateData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/updateSubjectChild`,
      type: "POST",
      data: subjectChildUpdateData,
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Datos actualizados",
        text: response.message,
      }).then(() => {
        $("#subjectsTable").DataTable().ajax.reload();
        $("#childSubjectsModal").modal("hide");
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al actualizar los datos",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al actualizar los datos de la materia",
      text: "Ocurrió un error al actualizar los datos de la materia, por favor intenta de nuevo más tarde.",
    });
  }
};

const AddSubjectChild = async (subjectChildData) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/addSubjectChild`,
      type: "POST",
      data: subjectChildData,
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Materia hija agregada",
        text: response.message,
      }).then(() => {
        $("#SubjectsChildAddModal").modal("hide");
        $("#addSubjectChild")[0].reset();
        $("#subjectsTable").DataTable().ajax.reload();
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al agregar la materia hija",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al agregar la materia hija",
      text: "Ocurrió un error al agregar la materia hija, por favor intenta de nuevo más tarde.",
    });
  }
};

const DeleteSubjectChild = async (subjectChildId, password) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/deleteSubjectChildById`,
      type: "POST",
      data: {
        subjectChildId: subjectChildId,
        password: password,
      },
    });
    if (response.success) {
      Swal.fire({
        icon: "success",
        title: "Materia hija eliminada",
        text: response.message,
      }).then(() => {
        $("#subjectsTable").DataTable().ajax.reload();
        $("#childSubjectsModal").modal("hide");
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al eliminar la materia hija",
        text: response.message,
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al eliminar la materia hija",
      text: "Ocurrió un error al eliminar la materia hija, por favor intenta de nuevo más tarde.",
    });
  }
};

const GetChildSubjectsData = async (subjectFatherId, subjectChildId) => {
  try {
    const response = await $.ajax({
      url: `${BASE_URL}/subject/getChildSubjectFindById`,
      type: "GET",
      data: {
        subjectFatherId: subjectFatherId,
        subjectChildId: subjectChildId,
      },
    });
    if (response.success) {
      FillChildInfo(response.data);
      childSubjectObserver.saveSnapshot();
    } else {
      Swal.fire({
        icon: "error",
        title: "Error al obtener los datos",
        text: response.message,
        confirmButtonText: "Iniciar sesión",
      }).then((result) => {
        if (result.isConfirmed) {
          window.location.href = "index.html";
        }
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Error al obtener los datos de la materia",
      text: "Ocurrió un error al obtener los datos de la materia, por favor intenta de nuevo más tarde.",
    });
  } finally {
    // Cierra loader
    $("#subjectChildEditLoader").hide();
  }
};

//miselanious

$("#SubjectsEditModal").on("hidden.bs.modal", function () {
  $("#updateSubject")[0].reset();
});

$("#childSubjectsModal").on("hidden.bs.modal", function () {
  $("#subjectChildInfo")[0].reset();
});

$("#SubjectsChildAddModal").on("hidden.bs.modal", function () {
  ClearSubjectChildInputs();
});
