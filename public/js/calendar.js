import { fullCalendar } from "./global/fullcalendar/index.js";
import { initializeDataTable } from "./global/dataTables.js";
import {
  errorAlert,
  infoAlert,
  loadingAlert,
  loadingSpinner,
  selectAlert,
  successAlertAuto,
  successAlertNoReload,
  warningAlert,
} from "./global/alerts.js";
import { sendFetch } from "./global/fetchCall.js";
import { validateForm } from "./global/validate/index.js";
import { loadModal } from "./utils/modalLoader.js";

var element = "#calendar";

$(function () {
  const calendarEl = document.getElementById("calendar");

  window.calendar = new FullCalendar.Calendar(calendarEl, {
    googleCalendarApiKey: "AIzaSyAZYDsaS_Gv_8vievQAPLB4Cd8D6K2AoAM",

    eventSources: [
      {
        googleCalendarId: "atencion-alumnos@esmefis.edu.mx",
        color: "#f9cb7d",
        display: "block",
        textColor: "#0951f5",
        className: "hvr-shrink val_c",
      },
    ],

    themeSystem: "bootstrap5",
    selectable: false,

    initialView: "dayGridWeek",
    views: {
      dayGridWeek: {
        duration: { days: 8 },
      },
    },
    timeZone: "local",
    locale: "es",

    hiddenDays: [6],

    displayEventEnd: true,

    businessHours: [
      // specify an array instead
      {
        daysOfWeek: [1, 2, 3, 4, 5],
        startTime: "09:00",
        endTime: "17:00",
      },
      {
        daysOfWeek: [7],
        startTime: "08:00",
        endTime: "14:00",
      },
    ],
    headerToolbar: {
      start: "title", // will normally be on the left. if RTL, will be on the right
      center: "",
      end: "prev,next today dayGridMonth,dayGridWeek,listWeek",
    },
    dateClick: async function (info) {
      loadingSpinner(true, "#addEventModalBody");
      $("#addEventModalLabel").html(
        "Agregar alumno para el " + info.dateStr + "",
      );
      $("#addEventModal").modal("show");
      await openAddEventModal({ date: info.dateStr });
    },

    eventClick: async function (info) {
      info.jsEvent.preventDefault();
      $("#eventDetailsBody").html("");
      loadingSpinner(true, "#eventDetailsBody");
      $("#eventDetails").modal("show");
      $("#eventDetailsLabel").html(info.event.title);
      try {
        await loadModal(
          `${BASE_URL}/api/modal/eventDetails`,
          {
          eventId: info.event._def.publicId,
          },
          "#eventDetailsBody",
        );
        initializeEventDetailsModal();
      } catch (error) {
        errorAlert(error.message);
        $("#eventDetails").modal("hide");
      }
    },
  });

  window.calendar.render();
});

$("#openAddEvent").on("click", function () {
  $("#addEventModalLabel").text("Agregar Evento");

  $("#addEventModalBody").html(`
        <div class="text-center p-3">
            <div class="spinner-border"></div>
        </div>
    `);

  $("#addEventModal").modal("show");

  openAddEventModal();
});

async function openAddEventModal(payload = {}) {
  try {
    await loadModal(`${BASE_URL}/api/modal/addEvent`, payload, "#addEventModalBody");
    initializeAddEventModal();
  } catch (error) {
    errorAlert(error.message);
    $("#addEventModal").modal("hide");
  }
}

function initializeAddEventModal() {
  $("#start, #end").timepicker({
    minTime: "09:00am",
    maxTime: "05:30pm",
    timeFormat: "H:i",
    showDuration: false,
  });

  $("#student")
    .select2({
      dropdownParent: $("#addEventModal"),
      theme: "bootstrap-5",
      placeholder: "Selecciona el alumno",
      ajax: {
        url: `${BASE_URL}/api/getStudentsListSelect`,
        type: "GET",
        dataType: "json",
        delay: 250,
        data: (params) => ({ search: params.term, page: params.page || 1 }),
        processResults: (data, params) => ({
          results: data.results,
          pagination: data.pagination,
        }),
        cache: true,
      },
      minimumInputLength: 2,
      language: {
        inputTooShort: () => "Por favor ingrese al menos 2 caracteres",
        searching: () => "Buscando...",
        noResults: () => "No se encontraron resultados.",
      },
    })
    .on("select2:select", (event) => {
      $("#studentName").val(event.params.data.text);
    });

  validateForm(
    "#addEvent",
    {
      date: { required: true },
      student: { required: true, valueNotEquals: "0" },
      start: { required: true },
      end: { required: true },
    },
    {
      date: { required: "Por favor, selecciona la fecha del evento." },
      student: { required: "Por favor, selecciona el alumno." },
      start: { required: "Por favor, selecciona la hora de ingreso." },
      end: { required: "Por favor, selecciona la hora de salida." },
    },
  );

  $("#addEvent").on("submit", async function (event) {
    event.preventDefault();
    if (!$(this).valid()) {
      infoAlert("Por favor, verifica que todos los campos sean correctos.");
      return;
    }

    const result = await Swal.fire({
      title: "¿Estás seguro de agregar el evento?",
      text: "Se registrará el evento en el calendario",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Sí, agregar",
      cancelButtonText: "Cancelar",
    });
    if (!result.isConfirmed) return;

    try {
      loadingAlert();
      const response = await sendFetch(`${BASE_URL}/api/addEvent`, "POST", {
        eventData: $(this).serialize(),
      });
      const data = await response.json();
      if (!data.success) throw new Error(data.message);
      successAlertAuto(data.message);
      $("#addEventModal").modal("hide");
      window.calendar.refetchEvents();
    } catch (error) {
      errorAlert(error.message);
    }
  });
}

async function initializeEventDetailsModal() {
  const eventId = $("#confirmEventForm").data("event-id");
  const $start = $("#startDetails");
  const $end = $("#endDetails");
  const $total = $("#totalHours");

  $("#startDetails, #endDetails").timepicker({
    minTime: "09:00am",
    maxTime: "06:00pm",
    timeFormat: "H:i",
    showDuration: false,
  });

  const calculate = () => {
    const duration = moment.duration(moment($end.val(), "HH:mm").diff(moment($start.val(), "HH:mm")));
    if (duration.asMinutes() <= 0) return warningAlert("La hora de salida debe ser posterior a la hora de entrada");
    if (Number.isNaN(duration.asMinutes())) return warningAlert("Asegúrese de que las horas sean válidas");
    const hours = Math.floor(duration.asHours());
    const minutes = Math.floor(duration.asMinutes()) - hours * 60;
    $total.val(`${String(hours).padStart(2, "0")}:${String(minutes).padStart(2, "0")}`);
  };

  $("#startDetails, #endDetails").on("changeTime", calculate);
  $("#btn_mas, #btn_menos").on("click", function () {
    $total.val(moment($total.val(), "HH:mm").add(Number($(this).data("fix")), "minutes").format("HH:mm"));
  });

  try {
    const response = await sendFetch(`${BASE_URL}/api/getEventDetails`, "GET", { eventId });
    const data = await response.json();
    if (!data.success) throw new Error(data.message);
    $start.val(data.data.start);
    $end.val(data.data.end);
    $("#startPlaceholder, #endPlaceholder").remove();
    $start.add($end).removeClass("d-none");
    calculate();
    if (data.confirmed) {
      infoAlert(data.message);
      $start.add($end).add($total).prop("disabled", true);
      $("#btn_mas, #btn_menos, #confirmHours, #deleteEvent").prop("hidden", true);
      $("#eventDetails").modal("hide");
    }
  } catch (error) {
    errorAlert(error.message);
    $("#eventDetails").modal("hide");
    return;
  }

  $("#confirmHours").on("click", async () => {
    try {
      loadingAlert();
      const hoursData = `${$("#confirmEventForm").serialize()}&eventId=${encodeURIComponent(eventId)}`;
      const response = await sendFetch(`${BASE_URL}/api/confirmHours`, "POST", { hoursData });
      const data = await response.json();
      if (!data.success) throw new Error(data.message);
      successAlertNoReload(data.message);
      $("#eventDetails").modal("hide");
      window.calendar.refetchEvents();
    } catch (error) {
      errorAlert(error.message);
    }
  });

  $("#deleteEvent").on("click", () => {
    selectAlert("Por favor selecciona el motivo de la cancelación", "Selecciona", {
      1: "Falta de asistencia",
      2: "Falta notificada",
      3: "Otro",
    }, "Confirmar", async (reason) => {
      try {
        loadingAlert();
        const hoursData = `${$("#confirmEventForm").serialize()}&eventId=${encodeURIComponent(eventId)}&deleteRazon=${encodeURIComponent(reason)}`;
        const response = await sendFetch(`${BASE_URL}/api/deleteEvent`, "POST", { hoursData });
        const data = await response.json();
        if (!data.success) throw new Error(data.message);
        successAlertNoReload(data.message);
        $("#eventDetails").modal("hide");
        window.calendar.refetchEvents();
      } catch (error) {
        errorAlert(error.message);
      }
    });
  });
}
