import { errorAlert, infoAlert, loadingAlert, successAlert } from "./utils/alerts.js";
import { initializeDataTable } from "./global/dataTables.js";
import { loadModal } from "./utils/modalLoader.js";

$("#carreersTable").on("click", ".subjectsCarreer", async function () {
  const careerId = $(this).data("id");

  try {
    await loadModal(`${BASE_URL}/carreer/modal/careersSubjects`, { careerId }, "#subjectsModalBody");
    initializeCareerSubjectsModal();
  } catch (error) {
    errorAlert(error.message);
    $("#subjectsModal").modal("hide");
  }
});

function initializeCareerSubjectsModal() {
  const careerId = $("#careerSubjectsContent").data("career-id");
  const $subject = $("#subjectName");
  let tableInitialized = false;

  $subject.select2({
    dropdownParent: $("#subjectsModal"),
    theme: "bootstrap-5",
    placeholder: "Selecciona una materia",
    ajax: {
      url: `${BASE_URL}/api/getSubjectsListSelect`,
      type: "POST",
      dataType: "json",
      delay: 250,
      data: (params) => ({ careerId, search: params.term, page: params.page || 1 }),
      processResults: (data) => ({ results: data.results, pagination: data.pagination }),
      cache: true,
    },
    minimumInputLength: 0,
    language: {
      searching: () => "Buscando...",
      noResults: () => "No se encontraron resultados.",
    },
  });

  const resetChildren = () => {
    $("#childSubjectName").empty().append('<option selected value="">Submateria</option>').prop("disabled", true);
  };

  $subject.on("select2:select", async (event) => {
    resetChildren();
    try {
      const subjects = await $.ajax({
        url: `${BASE_URL}/api/getChildSubject`,
        type: "POST",
        data: { subjectId: event.params.data.id },
      });
      if (!Array.isArray(subjects) || subjects[0]?.success === false) return;
      const $children = $("#childSubjectName");
      subjects.forEach((subject) => {
        if (subject.success !== false) {
          $children.append($("<option>", { value: subject.childSubjectId, text: subject.childSubjectName }));
        }
      });
      $children.prop("disabled", false).select2({
        dropdownParent: $("#subjectsModal"),
        theme: "bootstrap-5",
        placeholder: "Selecciona la submateria",
      });
    } catch (error) {
      errorAlert(error.message);
    }
  });
  $subject.on("select2:unselect select2:clear", resetChildren);

  $("#profile-tab").on("click", function () {
    if (tableInitialized) return;
    tableInitialized = true;
    initializeDataTable("#subjectsListTable", `${BASE_URL}/api/subjectsListTable`, { careerId }, [
      {
        data: "claveSubject",
        render: (data, type, row) => `<div class="ps-3"><div class="fw-600 pb-1">${row.claveSubject ?? ""}</div><p class="m-0 text-grey fs-09">${row.claveSubjectChild ?? ""}</p></div>`,
        className: "text-center py-2",
      },
      {
        data: "nombre",
        render: (data, type, row) => `<div class="ps-3"><div class="fw-600 pb-1">${row.nombre ?? ""}</div><p class="m-0 text-grey fs-09">${row.subject_child_nombre ?? ""}</p></div>`,
        className: "text-center py-2",
      },
    ]);
  });

  $("#addSubjectCareer").on("submit", async function (event) {
    event.preventDefault();
    try {
      loadingAlert();
      const data = await $.ajax({ url: `${BASE_URL}/api/addSubjectCareer`, type: "POST", data: $(this).serialize() });
      if (!data.success) throw new Error(data.message);
      if (data.error) infoAlert(data.error);
      successAlert(data.message);
      $("#subjectsModal").modal("hide");
      $("#carreersTable").DataTable().ajax.reload();
    } catch (error) {
      errorAlert(error.message);
    }
  });
}
