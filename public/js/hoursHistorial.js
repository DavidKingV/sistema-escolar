import { initializeDataTable } from "./global/dataTables.js";
import {
  confirmAlert,
  infoAlert,
  successAlertAuto,
  errorAlert,
  loadingAlert,
  loadingSpinner,
  warningAlert,
} from "./global/alerts.js";
import { sendFetch } from "./global/fetchCall.js";
import { loadModal } from "./utils/modalLoader.js";

$(function () {
  initializeDataTable(
    "#studentsHours",
    `${BASE_URL}/api/getStudentsHours`,
    {},
    [
      { data: "nombre", className: "text-center" },
      { data: "total_hours", className: "text-center" },
      { data: "date", className: "text-center" },
      {
        data: null,
        render: function (data, type, row) {
          return (
            `<button data-id="` +
            row.studentId +
            `" class="btn btn-info btn-circle addHours" data-bs-toggle="modal" data-bs-target="#addHoursModal"><i class="bi bi-plus-square"></i></button> 
            <button data-id="` +
            row.studentId +
            `" data-total="` +
            row.total_hours +
            `" class="btn btn-primary btn-circle seeTotal" data-bs-toggle="modal" data-bs-target="#seeTotalModal"><i class="bi bi-eye-fill"></i></button> 
            <button id="generateReport" data-id="` +
            row.studentId +
            `" class="btn btn-success btn-circle"><i class="bi bi-file-earmark-check-fill"></i></button>`
          );
        },
        className: "text-center",
      },
    ],
  );

  $("#studentsHours").on("click", ".seeTotal", function () {
    const id = $(this).data("id");
    const total = $(this).data("total");
    modalTotal(id, total);
  });

  $("#studentsHours").on("click", ".addHours", function () {
    const id = $(this).data("id");
    addHours(id);
  });
});

const modalTotal = async (id, total) => {
  loadingSpinner(true, "#seeTotalModalBody");
  try {
    await loadModal(`${BASE_URL}/api/modal/seeTotal`, {
      studentId: id,
      totalHours: total,
    }, "#seeTotalModalBody");
    initializeTotalModal();
  } catch (error) {
    errorAlert(error.message);
    $("#seeTotalModal").modal("hide");
  }
};

const addHours = async (id) => {
  loadingSpinner(true, "#addHoursModalBody");
  try {
    await loadModal(`${BASE_URL}/api/modal/addHours`, { studentId: id }, "#addHoursModalBody");
    initializeAddHoursModal();
  } catch (error) {
    errorAlert(error.message);
    $("#addHoursModal").modal("hide");
  }
};

async function getStudentName(studentId, target) {
  const response = await sendFetch(`${BASE_URL}/api/getStudentName`, "GET", { studentId });
  const data = await response.json();
  if (!data.success) throw new Error(data.message);
  $(target).text(data.studentName);
}

function initializeAddHoursModal() {
  const studentId = $("#addHoursForm").data("student-id");
  const $start = $("#start");
  const $end = $("#end");
  const $total = $("#totalHours");

  getStudentName(studentId, "#studentNameH").catch((error) => errorAlert(error.message));
  $start.add($end).timepicker({ minTime: "09:00am", maxTime: "06:00pm", timeFormat: "H:i", showDuration: false });

  const calculate = () => {
    const duration = moment.duration(moment($end.val(), "HH:mm").diff(moment($start.val(), "HH:mm")));
    if (duration.asMinutes() <= 0) return warningAlert("La hora de salida debe ser posterior a la hora de entrada");
    if (Number.isNaN(duration.asMinutes())) return warningAlert("Asegúrese de que las horas sean válidas");
    const hours = Math.floor(duration.asHours());
    const minutes = Math.floor(duration.asMinutes()) - hours * 60;
    $total.val(`${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`);
  };

  $start.add($end).on("changeTime", calculate);
  $end.on("keyup", () => !$end.val() && $total.val(""));
  $("#btn_mas, #btn_menos").on("click", function () {
    $total.val(moment($total.val(), "HH:mm").add(Number($(this).data("ajuste")), "minutes").format("HH:mm"));
  });

  $("#addHoursForm").on("submit", async function (event) {
    event.preventDefault();
    try {
      loadingAlert();
      const data = `${$(this).serialize()}&studentId=${encodeURIComponent(studentId)}`;
      const response = await sendFetch(`${BASE_URL}/api/addStudentHours`, "POST", { data });
      const result = await response.json();
      if (!result.success) throw new Error(result.message);
      $("#addHoursModal").modal("hide");
      $("#studentsHours").DataTable().ajax.reload();
      successAlertAuto(result.message);
    } catch (error) {
      errorAlert(error.message);
    }
  });
}

async function initializeTotalModal() {
  const studentId = $("#studentHoursContent").data("student-id");
  await initializeDataTable("#studentHoursDataTable", `${BASE_URL}/api/getStudentlHoursData`, { studentId }, [
    { data: "date", className: "text-center" },
    { data: "hours", className: "text-center" },
    { data: "status", className: "text-center" },
    {
      data: "actions",
      render: (data, type, row) => data ? `<button data-id="${row.id}" class="btn btn-danger btn-circle deleteHour"><i class="bi bi-trash-fill"></i></button>` : "",
      className: "text-center",
    },
  ]);

  getStudentName(studentId, "#seeTotalModalLabel").catch((error) => errorAlert(error.message));
  $("#studentHoursDataTable").on("click", ".deleteHour", function () {
    const hourId = $(this).data("id");
    confirmAlert("¿Estás seguro de eliminar esta hora?", "Sí, eliminar", "Cancelar", async () => {
      try {
        const response = await sendFetch(`${BASE_URL}/api/deleteHour`, "POST", { hourId });
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        successAlertAuto(data.message);
        $("#studentHoursDataTable").DataTable().ajax.reload();
      } catch (error) {
        errorAlert(error.message);
      }
    });
  });
}
