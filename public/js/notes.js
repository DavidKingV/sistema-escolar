import { initializeDataTable } from "./global/dataTables.js";
import {
  confirmAlert,
  infoAlert,
  loadingAlert,
  successAlert,
  successAlertAuto,
  errorAlert,
  loadingSpinner,
} from "./global/alerts.js";
import { sendFetch } from "./global/fetchCall.js";
import { validateForm } from "./global/validate/index.js";
import { loadModal } from "./utils/modalLoader.js";

let urlParams = new URLSearchParams(window.location.search);
let studentIdGroup = urlParams.get("student");

let studentName;

$(function () {
  getStudentName(studentIdGroup);
});

const getStudentName = async (studentIdGroup) => {
  try {
    const response = await sendFetch(
      `${BASE_URL}/api/getStudentName`,
      "GET",
      {
        studentId: studentIdGroup,
      },
    );

    if (!response.ok) {
      throw new Error(
        "Ocurrió un error al realizar la petición: " + response.statusText,
      );
    }

    const data = await response.json();

    if (data.success) {
      studentName = data.studentName;

      $("#placeholder")
        .text("Calificaciones de " + data.studentName)
        .attr("class", "studentName");
    } else {
      errorAlert(data.message);
    }
  } catch (error) {
    errorAlert(error);
  }
};

$("#studentGradesTable").on("click", ".studentGrade", async function () {
  let subjectId = $(this).data("subject");
  let subjectName = $(this).data("subjectname");

  let subjectChildId = $(this).data("subjectchild");
  let subjectChildName = $(this).data("subjectchildname");

  let gradeId = $(this).data("grade");

  $("#makeOverExamModalLabel").html(
    "Recursamiento para " +
      studentName +
      " - " +
      subjectName +
      "  " +
      (subjectChildName ? subjectChildName : ""),
  );
  $("#makeOverExamModal").modal("show");
  try {
    await loadModal(
      `${BASE_URL}/api/modal/makeOverExam`,
      {
      studentId: studentIdGroup,
      subjectId: subjectId,
      subjectName: subjectName,
      subjectChildName: subjectChildName,
      subjectChildId: subjectChildId,
      gradeId: gradeId,
      },
      "#makeOverExamModalBody",
    );
    initializeMakeOverExamModal();
  } catch (error) {
    errorAlert(error.message);
    $("#makeOverExamModal").modal("hide");
  }
});

$("#studentGradesTable").on("click", ".mekeOver", async function () {
  let makeOverId;

  if ($(this).data("makeoverid")) {
    makeOverId = $(this).data("makeoverid");
  } else {
    makeOverId = null;
  }

  $("#makeOverViewModalLabel").html(
    "calificacion de Recursamiento para " + studentName,
  );
  $("#makeOverViewModal").modal("show");
  await openMakeOverDetails({ makeOverId });
});

$("#studentGradesTable").on("click", ".makeOverChild", async function () {
  let makeOverChildId;

  if ($(this).data("makeoverchildid")) {
    makeOverChildId = $(this).data("makeoverchildid");
  } else {
    makeOverChildId = null;
  }

  $("#makeOverViewModalLabel").html(
    "calificacion de Recursamiento para " + studentName,
  );
  $("#makeOverViewModal").modal("show");
  await openMakeOverDetails({ makeOverChildId });
});

function initializeMakeOverExamModal() {
  validateForm(
    "#addMakeOverGrade",
    {
      continuosGrade: { required: true, number: true, min: 0, max: 10 },
      examGrade: { required: true, number: true, min: 0, max: 10 },
      finalGrade: { required: true, number: true, min: 0, max: 10 },
    },
    {
      continuosGrade: { required: "La calificación continua es requerida", number: "Debe ser un número", min: "El mínimo es 0", max: "El máximo es 10" },
      examGrade: { required: "La calificación del examen es requerida", number: "Debe ser un número", min: "El mínimo es 0", max: "El máximo es 10" },
      finalGrade: { required: "La calificación final es requerida", number: "Debe ser un número", min: "El mínimo es 0", max: "El máximo es 10" },
    },
  );

  $("#continuosGrade, #examGrade").on("input", function () {
    const continuous = Number.parseFloat($("#continuosGrade").val());
    const exam = Number.parseFloat($("#examGrade").val());
    $("#finalGrade").val(Number.isNaN(continuous + exam) ? "" : (continuous + exam) / 2);
  });

  $("#addMakeOverGrade").on("submit", async function (event) {
    event.preventDefault();
    if (!$(this).valid()) {
      infoAlert("Por favor completa los campos correctamente");
      return;
    }
    try {
      loadingAlert();
      const response = await sendFetch(`${BASE_URL}/api/addMakeOverGrade`, "POST", {
        makeOverData: $(this).serialize(),
      });
      const data = await response.json();
      if (!data.success) throw new Error(data.message);
      if (data.error) infoAlert(data.error);
      $("#makeOverExamModal").modal("hide");
      successAlert(data.message);
      $("#gradesStudentTable").DataTable().ajax.reload();
    } catch (error) {
      errorAlert(error.message);
    }
  });
}

async function openMakeOverDetails(payload) {
  try {
    await loadModal(`${BASE_URL}/api/modal/viewMakeOver`, payload, "#makeOverViewModalBody");
    const makeOverId = $("#makeOverDetails").data("make-over-id");
    const response = await sendFetch(`${BASE_URL}/api/getMakeOverDetails`, "GET", { makeOverId });
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    const grade = data.grades[0];
    $("#viewMakeOverSubjectName").val(grade.subject_nombre);
    $("#viewMakeOverSubjectChildName").val(grade.subject_child_nombre);
    $("#viewMakeOverContinuosGrade").val(grade.continuosGrade);
    $("#viewMakeOverExamGrade").val(grade.examGrade);
    $("#viewMakeOverFinalGrade").val(grade.finalGrade);
  } catch (error) {
    errorAlert(error.message);
    $("#makeOverViewModal").modal("hide");
  }
}
