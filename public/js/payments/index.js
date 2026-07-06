/**
 * index.optimized.js
 * Optimizado para rendimiento, legibilidad y seguridad.
 * - Peticiones centralizadas con manejo de errores.
 * - Sanitización básica de entradas/salidas.
 * - Prevención de XSS en renderers.
 * - Eliminación de redundancias y logs sensibles.
 */

import { enviarPeticionAjaxAction } from "../utils/ajax.js";
import { sendFetch } from "../utils/fetch.js";
import {
  successAlert,
  errorAlert,
  loadingAlert,
  infoAlert,
} from "../utils/alerts.js";
import {
  validateForm,
  capitalizeFirstLetter,
} from "../global/validate/index.js";
import { initializeDataTable } from "../global/dataTables.js";
import { sendNewPayment } from "./newPayment.js";

// =======================
// Config
// =======================
const phpPath = "../../backend/payments/routes.php";
const studentsPath = "../../backend/students/routes.php";
const apiPath = "../api.php";
const DEBUG = false; // Cambia a true solo durante desarrollo

// =======================
// Utils (seguridad y helpers)
// =======================
const log = (...args) => {
  if (DEBUG) console.log("[DEBUG]", ...args);
};

const escapeHtml = (str) => {
  return String(str)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
};

const sanitize = (v) => (typeof v === "string" ? v.replace(/[<>]/g, "") : v);

const isValidId = (id) => /^\d+$/.test(String(id));
const isValidDay = (day) => Number.isFinite(+day) && +day >= 1 && +day <= 31;

const toMysqlDateWithDay = (day) => {
  const d = new Date();
  d.setHours(0, 0, 0, 0);
  d.setDate(Number(day));
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, "0");
  const dd = String(d.getDate()).padStart(2, "0");
  return `${yyyy}-${mm}-${dd}`;
};

const showLoader = () => $("#globalLoader").fadeIn(200);
const hideLoader = () => $("#globalLoader").fadeOut(200);

// Petición centralizada con sendFetch (mantiene cookies/CSRF definidos en tu helper)
const requestJson = async (url, action, payload = {}) => {
  try {
    const res = await sendFetch(url, "POST", { action, ...payload });
    const data = await res.json();
    if (!data || typeof data !== "object")
      throw new Error("Respuesta inválida del servidor");
    return data;
  } catch (err) {
    log("requestJson error:", err);
    throw err;
  }
};

// =======================
// Variable global para snapshot
// =======================
let originalPaymentData = {};

const hasPaymentChanges = () => {
  const currentData = getCurrentPaymentFormData();

  return Object.keys(originalPaymentData).some(
    (key) => currentData[key] !== originalPaymentData[key],
  );
};

const observePaymentChanges = () => {
  $("#updatePaymentStudent")
    .find("input, textarea, select")
    .off("input change")
    .on("input change", () => {
      setTimeout(() => {
        const changed = hasPaymentChanges();
        $("#savePaymentChanges").prop("disabled", !changed);
      }, 0);
    });
};

// =======================
// Flujo: Estudiantes
// =======================
const getStudentsNames = async () => {
  try {
    showLoader();
    const students = await requestJson(studentsPath, "getStudentsNames");

    if (!Array.isArray(students) || students.length === 0) return;

    const $select = $("#studentName");
    const fragment = $(document.createDocumentFragment());

    students.forEach((student) => {
      if (student && student.success !== false) {
        fragment.append(
          new Option(escapeHtml(student.name), String(student.id)),
        );
      }
    });

    $select.append(fragment).select2({ theme: "bootstrap-5" });

    // Cuando se elija un alumno, verificar si tiene configuración de pago mensual
    $select.on("change", async function () {
      $("#paymentConcept").prop("disabled", false);
      $("#paymentMonth").prop("disabled", false);
      const studentId = $(this).val();
      if (isValidId(studentId)) {
        await VerifyMonthlyPayment(studentId);
      } else {
        infoAlert("Estudiante inválido.");
      }
    });
  } catch (error) {
    errorAlert("No se pudieron cargar los estudiantes.");
  } finally {
    hideLoader();
  }
};

// =======================
// UI helpers (placeholders)
// =======================
function showLoadedContent() {
  $("#paymentDaysPlaceholder").addClass("d-none");
  $("#paymentHistoryPlaceholder").addClass("d-none");
  $("#paymentDaysContent").removeClass("d-none");
  $("#paymentHistoryTable").removeClass("d-none");
}

function hideLoadedContent() {
  $("#paymentDaysPlaceholder").removeClass("d-none");
  $("#paymentHistoryPlaceholder").removeClass("d-none");
  $("#paymentDaysContent").addClass("d-none");
  $("#paymentHistoryTable").addClass("d-none");
}

// =======================
// Pagos
// =======================
const AddPayment = async (serializedForm) => {
  // Mantenemos enviarPeticionAjaxAction para no romper backend existente que espera form-encoded
  loadingAlert();
  try {
    const resp = await enviarPeticionAjaxAction(
      phpPath,
      "POST",
      "AddPayment",
      serializedForm,
    );
    const response = typeof resp === "string" ? JSON.parse(resp) : resp;

    if (response?.success) {
      $("#studentPaymentDate")[0]?.reset();
      $("#paymentsForm")[0].reset();
      $("#paymentHistoryTable").DataTable().destroy();
      $("#paymentConcept").prop("disabled", true);
      $("#studentName").val("0").trigger("change.select2");
      $("#subjectConceptDiv").prop("hidden", true);
      $("#subjectConcept").prop("disabled", true).trigger("change");
      $("#childSubjectDiv").prop("hidden", true);
      $("#childSubjectName").prop("disabled", true);
      $("#careerDiv").prop("hidden", true);
      $("#careerName").prop("readonly", true).val("");
      $("#paymentMonth").prop("disabled", true);
      hideLoadedContent();

      const dataObj = Object.fromEntries(new URLSearchParams(serializedForm));

      if (dataObj.toEmail === "on") {
        try {
          const sent = await sendNewPayment(
            sanitize(dataObj.studentName),
            response.paymentId,
          );
          if (sent) {
            successAlert(
              "Pago registrado y comprobante enviado correctamente.",
            );
          } else {
            infoAlert(
              "Pago registrado, pero no se pudo enviar el comprobante.",
            );
          }
        } catch (e) {
          infoAlert("Pago registrado, pero falló el envío del comprobante.");
        }
      } else {
        successAlert(escapeHtml(response.message || "Pago registrado."));
      }
    } else {
      errorAlert(
        escapeHtml(response?.message || "No se pudo registrar el pago."),
      );
    }
  } catch (err) {
    errorAlert("Error inesperado al registrar el pago.");
  }
};

const UpdatePayment = async (serializedForm) => {
  try {
    const resp = await enviarPeticionAjaxAction(
      phpPath,
      "POST",
      "UpdatePayment",
      serializedForm,
    );

    const response = typeof resp === "string" ? JSON.parse(resp) : resp;

    if (response.success) {
      successAlert(response.message);

      $("#studentPaymentEditModal").modal("hide");

      $("#paymentHistoryStudentTable").DataTable().ajax.reload(null, false);
      $("#studentPaymentsModal").modal("show");
    } else {
      errorAlert(response.message || "No se pudo actualizar.");
    }
  } catch (error) {
    errorAlert("Error inesperado al actualizar el pago.");
  }
};

const VerifyMonthlyPayment = async (studentId) => {
  try {
    loadingAlert();

    if (!isValidId(studentId)) {
      infoAlert("Estudiante inválido.");
      resetPaymentDaysForm();
      return;
    }

    const response = await requestJson(phpPath, "VerifyMonthlyPayment", {
      studentId,
    });

    if (!response.success) {
      infoAlert(
        escapeHtml(response.message || "No hay configuración de pago."),
      );
      resetPaymentDaysForm();
      return;
    }

    $("#studentId").val(studentId);
    $("#paymentDaysCard").prop("hidden", false);

    if (
      response.payment_day > 0 &&
      response.concept?.trim() !== "0" &&
      parseFloat(response.monthly_amount) > 0
    ) {
      $("#savePaymentDays").prop("disabled", true);
      $("#updatePaymentDays").prop("disabled", false);
    } else {
      $("#savePaymentDays").prop("disabled", false);
      $("#updatePaymentDays").prop("disabled", true);
    }

    $("#paymentDay").val(sanitize(response.payment_day));
    $("#paymentConceptDay").val(sanitize(response.concept)).trigger("change");
    $("#paymentAmountDay").val(sanitize(response.monthly_amount));

    $("#paymentConcept").val(sanitize(response.concept)).trigger("change");

    const currentMonth = new Date().toLocaleString("default", {
      month: "long",
    });
    $("#paymentMonth")
      .val(capitalizeFirstLetter(currentMonth))
      .trigger("change");
    changeCurrentMonth();

    $("#paymentPrice").val(sanitize(response.monthly_amount));
    CalculateTotal();

    // Inicializar DataTable con renderers seguros
    initializeDataTable(
      "#paymentHistoryTable",
      phpPath,
      { action: "GetPaymentHistory", studentId },
      [
        {
          data: null,
          className: "text-center",
          render: (row) => {
            let concept = row.concept;

            if (row.concept_subject) {
              concept += ` - Materia: ${row.concept_subject}`;
            }

            if (row.concept_subject_child) {
              concept += ` | Submateria: ${row.concept_subject_child}`;
            }

            if (row.concept_carreer) {
              concept += ` | ${row.concept_carreer}`;
            }

            if (row.concept_month) {
              concept += ` ${row.concept_month}`;
            }

            return escapeHtml(concept);
          },
        },
        {
          data: "amount",
          className: "text-center",
          render: (d) =>
            $.fn.dataTable.render.number(",", ".", 2, "$").display(d),
        },
        {
          data: "payment_date",
          className: "text-center",
          render: (d) => escapeHtml(d),
        },
        {
          data: "payment_method",
          className: "text-center",
          render: (d) => {
            const methods = {
              1: "Efectivo",
              3: "Transferencia bancaria",
              4: "Tarjeta de crédito",
              28: "Tarjeta de débito",
            };

            return escapeHtml(methods[String(d)] || "Desconocido");
          },
        },
        {
          data: "status",
          className: "text-center",
          render: function (data, type, row) {
            const statuses = {
              confirmed: { label: "Confirmado", badge: "text-bg-success" },
              pending: { label: "Pendiente", badge: "text-bg-primary" },
              cancelled: { label: "Cancelado", badge: "text-bg-warning" },
            };

            const status = statuses[String(data)];

            if (status)
              return `<span class="badge ${status.badge}" data-status="${data}">${status.label}</span>`;
            else
              return `<span class="badge text-bg-secondary" data-status="${data}">Desconocido</span>`;
          },
        },
        {
          data: null,
          className: "text-center",
          orderable: false,
          render: (_, __, row) => {
            const pid = escapeHtml(String(row.id ?? ""));
            const sid = escapeHtml(String(row.id_student ?? ""));
            return `<button class="btn btn-sm btn-primary sendPayment" data-id="${pid}" data-student="${sid}">Enviar</button>`;
          },
        },
      ],
    );

    showLoadedContent();
    await SurchargeForLatePayment(response.payment_day, studentId);
  } catch (error) {
    errorAlert("Ocurrió un error inesperado al verificar el pago mensual.");
  } finally {
    //Swal.close?.();
  }
};

const VerifyPaymentsForStudent = async (paymentId, studentId, action) => {
  try {
    if (!isValidId(studentId)) {
      infoAlert("Estudiante inválido.");
      return;
    }

    const response = await requestJson(phpPath, "VerifyMonthlyPayment", {
      studentId,
    });

    if (!response.success) {
      infoAlert(
        escapeHtml(response.message || "No hay configuración de pago."),
      );
      return;
    }

    if (action === "view") {
      $("#idStudentDB").val(studentId);

      // Inicializar DataTable con renderers seguros
      initializeDataTable(
        "#paymentHistoryStudentTable",
        phpPath,
        { action: "GetPaymentHistory", studentId },
        [
          {
            data: null,
            className: "text-center",
            render: (row) => {
              let concept = row.concept;

              if (row.concept_subject) {
                concept += ` - Materia: ${row.concept_subject}`;
              }

              if (row.concept_subject_child) {
                concept += ` | Submateria: ${row.concept_subject_child}`;
              }

              if (row.concept_carreer) {
                concept += ` | ${row.concept_carreer}`;
              }

              if (row.concept_month) {
                concept += ` ${row.concept_month}`;
              }

              return escapeHtml(concept);
            },
          },
          {
            data: "amount",
            className: "text-center",
            render: (d) =>
              $.fn.dataTable.render.number(",", ".", 2, "$").display(d),
          },
          {
            data: "payment_date",
            className: "text-center",
            render: (d) => escapeHtml(d),
          },
          {
            data: "payment_method",
            className: "text-center",
            render: (d) => {
              const methods = {
                1: "Efectivo",
                3: "Transferencia bancaria",
                4: "Tarjeta de crédito",
                28: "Tarjeta de débito",
              };

              return escapeHtml(methods[String(d)] || "Desconocido");
            },
          },
          {
            data: "invoice",
            className: "text-center",
            render: (d, _, row) => {
              const invoiceStatus = {
                0: "Sin valor fiscal",
                1: "Cambio por factura",
              };

              const status = Number(d);

              // Texto seguro
              const label = escapeHtml(invoiceStatus[status] || "Desconocido");

              // Si es 0, agregar botón
              if (status === 0) {
                return `
                <div class="d-flex flex-column align-items-center gap-1">
                  <span>${label}</span>
                  <button 
                    class="btn btn-sm btn-success generateInvoice"
                    data-id="${escapeHtml(String(row.id ?? ""))}">
                    Generar
                  </button>
                </div>
              `;
              }
              return label;
            },
          },
          {
            data: "status",
            className: "text-center",
            render: function (data, type, row) {
              const statuses = {
                confirmed: { label: "Confirmado", badge: "text-bg-success" },
                pending: { label: "Pendiente", badge: "text-bg-primary" },
                cancelled: { label: "Cancelado", badge: "text-bg-warning" },
              };

              const status = statuses[String(data)];

              if (status)
                return `<span class="badge ${status.badge}" data-status="${data}">${status.label}</span>`;
              else
                return `<span class="badge text-bg-secondary" data-status="${data}">Desconocido</span>`;
            },
          },
          {
            data: null,
            className: "text-center",
            orderable: false,
            render: (_, __, row) => {
              const pid = escapeHtml(String(row.id ?? ""));
              const sid = escapeHtml(String(row.id_student ?? ""));
              const date = escapeHtml(String(row.payment_date ?? ""));

              return `
              <div class="dropdown">
                  <button class="btn btn-secondary btn-circle dropdown-toggle" type="button" data-bs-toggle="dropdown">
                      <i class="bi bi-list"></i>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-end">
                      <li>
                        <a class="dropdown-item editStudentPayment" href="#" data-id="${pid}" data-student-id="${sid}" data-payment-date="${date}" data-bs-toggle="modal" data-bs-target="#studentPaymentEditModal">
                          <i class="bi bi-pencil-square"></i> Editar
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item deleteStudentPayment" href="#" data-id="${pid}">
                          <i class="bi bi-trash-fill"></i> Eliminar
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item sendPayment" href="#" data-id="${pid}" data-student="${sid}">
                          <i class="bi bi-envelope"></i> Enviar
                        </a>
                      </li>
                  </ul>
              </div>
            `;
            },
          },
        ],
      );
    } else {
      const paymentResponse = await requestJson(phpPath, "GetPaymentHistory", {
        studentId,
        paymentId,
      });

      // Validar respuesta
      if (
        !paymentResponse.success ||
        !Array.isArray(paymentResponse.data) ||
        paymentResponse.data.length === 0
      ) {
        infoAlert("No se encontró el pago.");
        return;
      }

      // Obtener el primer pago
      const paymentData = paymentResponse.data[0];
      const fullConcept = [
        paymentData.concept,
        paymentData.concept_subject
          ? `- Materia: ${paymentData.concept_subject}`
          : null,
        paymentData.concept_subject_child
          ? `| Submateria: ${paymentData.concept_subject_child}`
          : null,
        paymentData.concept_carreer ? `| ${paymentData.concept_carreer}` : null,
        paymentData.concept_month,
      ]
        .filter(Boolean)
        .join(" ");

      // Pintar datos
      $("#idPayment").val(sanitize(paymentData.id));
      $("#paymentConcept").text(sanitize(fullConcept));
      $("#paymentPrice").val(sanitize(paymentData.cost));

      const extraValue = parseFloat(sanitize(paymentData.extra)) || 0;
      const isZero = extraValue === 0;

      $("#paymentExtra").val(extraValue.toFixed(2));
      $("#paymentExtraCkeck").prop("checked", isZero);
      $("#paymentExtra").prop("disabled", isZero);

      $("#paymentExtra").val(sanitize(paymentData.extra));
      $("#paymentTotal").val(sanitize(paymentData.amount));
      $("#paymentMethod")
        .val(sanitize(paymentData.payment_method))
        .trigger("change");
      $("#paymentComments").val(sanitize(paymentData.comments ?? ""));

      const invoiceLabels = { 0: "Recibo", 1: "Factura" };
      const invoiceKey = parseInt(paymentData.invoice);
      $("#paymentInvoice").text(invoiceLabels[invoiceKey] ?? "Desconocido");

      if (paymentData.status === "cancelled") {
        $("#cancelReceipt").prop("hidden", true);
        $("#savePaymentChanges").prop("hidden", true);
      } else {
        $("#cancelReceipt").prop("hidden", false);
        $("#savePaymentChanges").prop("hidden", false);
      }

      setTimeout(() => {
        originalPaymentData = getCurrentPaymentFormData();
        $("#savePaymentChanges").prop("disabled", true);
      }, 0);
    }
  } catch (error) {
    errorAlert("Ocurrió un error inesperado al verificar el pago mensual.");
  } finally {
    Swal.close?.();
  }
};

const sendPaymentByEmail = async (studentId, paymentId) => {
  try {
    if (!isValidId(studentId) || !isValidId(paymentId)) {
      return errorAlert("Identificador inválido");
    }

    loadingAlert();

    const response = await requestJson(phpPath, "SendPaymentByEmail", {
      studentId,
      paymentId,
    });

    if (response.success) {
      successAlert(escapeHtml(response.message));
    } else {
      errorAlert(escapeHtml(response.message));
    }
  } catch (error) {
    errorAlert("Ocurrió un error inesperado al enviar el comprobante");
  } finally {
    // Swal.close?.();
  }
};

const toggleInputOnCheckboxChange = (check, input) => {
  const isChecked = check.prop("checked");
  input.prop("disabled", isChecked);
  if (isChecked) input.val("");
};

const CalculateTotal = () => {
  const price = parseFloat($("#paymentPrice").val()) || 0;
  const $extra = $("#paymentExtra");
  const extra =
    $extra.length && !$extra.prop("disabled")
      ? parseFloat($extra.val()) || 0
      : 0;
  const total = price + extra;
  $("#paymentTotal").val(total.toFixed(2));
};

const toggleInput = (selector, disabled, value = null) => {
  $(selector).prop("disabled", disabled);
  if (value !== null) $(selector).val(value);
};

const SurchargeForLatePayment = async (paymentDay, studentId) => {
  try {
    const mysqlDate = toMysqlDateWithDay(paymentDay);
    const payload = { data: { studentId, paymentDay: mysqlDate } };
    const response = await requestJson(phpPath, "CheckIfPaymentMade", payload);

    if (!response.success) {
      return infoAlert(
        escapeHtml(response.message || "No se pudo verificar el recargo."),
      );
    }

    const messages = {
      ON_TIME: "El pago de este mes ya ha sido realizado a tiempo.",
      EXTEMPORANEO:
        "El pago de este mes ya ha sido realizado, pero fue tarde. Se aplicó un recargo por pago tardío.",
      PENDING: (day) =>
        `El pago de este mes aún no se ha realizado. Si se paga después del día ${day ?? "SIN DEFINIR"}, se aplicará un recargo.`,
    };

    const msg =
      typeof messages[response.data?.status] === "function"
        ? messages[response.data.status](paymentDay)
        : messages[response.data?.status] || "Estado no reconocido.";

    infoAlert(msg);
  } catch {
    infoAlert("No se pudo verificar el recargo por pago tardío.");
  }
};

const changeCurrentMonth = () => {
  let currentMonth = new Date().toLocaleString("default", { month: "long" });
  currentMonth = capitalizeFirstLetter(currentMonth);
  if (currentMonth !== $("#paymentMonth").val()) {
    $("#todayDate").prop("checked", false);
    $("#paymentDate").prop("disabled", false);
  } else {
    $("#todayDate").prop("checked", true);
    $("#paymentDate").prop("disabled", true);
  }
};

const handlePaymentDayAction = async (actionType) => {
  const studentId = $("#studentId").val();
  const paymentDay = $("#paymentDay").val();
  const paymentConcept = $("#paymentConceptDay").val();
  const paymentAmount = $("#paymentAmountDay").val();

  if (!isValidId(studentId)) return errorAlert("Estudiante inválido.");
  if (!isValidDay(Number(paymentDay)))
    return errorAlert("Por favor, ingrese un día válido (1-31).");

  const payload = {
    data: {
      studentId: String(studentId),
      paymentDay: String(paymentDay),
      paymentConcept: sanitize(paymentConcept),
      paymentAmount: String(paymentAmount),
    },
  };

  loadingAlert();
  try {
    const resp = await requestJson(phpPath, "savePaymentDays", payload);
    if (resp.success) {
      successAlert(escapeHtml(resp.message));

      $("#paymentConcept").val(payload.data.paymentConcept);
      $("#paymentPrice").val(payload.data.paymentAmount);
      $("#paymentTotal").val(payload.data.paymentAmount);

      const hasValidPaymentData =
        Number(payload.data.paymentDay) > 0 &&
        payload.data.paymentConcept?.trim() !== "0" &&
        parseFloat(payload.data.paymentAmount) > 0;

      $("#savePaymentDays").prop("disabled", hasValidPaymentData);
      $("#updatePaymentDays").prop("disabled", !hasValidPaymentData);
    } else {
      errorAlert(
        escapeHtml(
          resp.message ||
            `Error al ${actionType === "save" ? "reservar" : "actualizar"}`,
        ),
      );
    }
  } catch (err) {
    errorAlert("Error de red al procesar la solicitud.");
  } finally {
    // Swal.close?.();
  }
};

// =======================
// API académica (grupos/materias)
// =======================
const getGroupCareer = async (studentId) => {
  try {
    if (!isValidId(studentId)) throw new Error("ID inválido");
    return await requestJson(apiPath, "getGroupCareer", { studentId });
  } catch (error) {
    log(error);
    return { success: false, message: "No se pudo obtener el grupo/carrera." };
  }
};

const GetChildSubject = async (subjectId) => {
  try {
    const subjectsList = await requestJson(phpPath, "getChildSubject", {
      subjectId,
    });
    if (
      !subjectsList ||
      subjectsList.success === false ||
      !subjectsList.length
    ) {
      $("#childSubjectName").prop("disabled", true);
      return;
    }

    const $select = $("#childSubjectName");
    $select.empty();

    $.each(subjectsList, function (_index, subject) {
      if (subject?.success !== false) {
        $select.append(
          $("<option>", {
            value: String(subject.childSubjectName),
            text: escapeHtml(subject.childSubjectName),
          }),
        );
      }
    });

    $select.select2({
      theme: "bootstrap-5",
      placeholder: "Selecciona la submateria",
    });

    $("#childSubjectDiv").prop("hidden", false);
    $("#childSubjectName").prop("disabled", false);
  } catch (error) {
    errorAlert("Error al cargar submaterias.");
  }
};

const getSubjectsList = async (input, careerId) => {
  try {
    input.select2({
      theme: "bootstrap-5",
      placeholder: "Selecciona una materia",
      ajax: {
        url: apiPath,
        type: "POST",
        dataType: "json",
        delay: 250,
        data: (params) => ({
          action: "getSubjectsListSelect",
          careerId,
          search: params.term,
          page: params.page || 1,
        }),
        processResults: (data, params) => {
          params.page = params.page || 1;

          const results = data.results.map((item) => ({
            id: item.text, // 👈 usar el nombre como value
            text: item.text, // 👈 mostrar también el nombre
          }));

          return { results, pagination: data.pagination };
        },
        cache: true,
      },
      minimumInputLength: 2, // evitar querys vacías
      language: {
        inputTooShort: () => "Por favor ingrese al menos 2 caracteres",
        searching: () => "Buscando...",
        noResults: () => "No se encontraron resultados.",
      },
    });

    input.on("select2:select", (e) => GetChildSubject(e.params.data.id));

    const resetChild = () => {
      $("#childSubjectName")
        .empty()
        .append('<option selected value="">Submateria</option>')
        .prop("disabled", true);
    };
    input.on("select2:unselect", resetChild);
    input.on("select2:clear", resetChild);
    input.on("change", resetChild);
  } catch (error) {
    errorAlert("Error al inicializar la búsqueda de materias.");
  }
};

// =======================
// DOM Ready
// =======================
$(document).ready(function () {
  // Conceptos que abren selects dinámicos
  $("#paymentConcept").on("change", async function () {
    const concept = $(this).val();
    const studentId = $("#studentName").val();

    const ensureStudentSelected = () => {
      $("#subjectConceptDiv").prop("hidden", true);
      $("#childSubjectName").prop("disabled", true);
      $("#careerName").prop("readonly", true);
      $("#paymentConcept").val(0).trigger("change");
      infoAlert("Por favor, seleccione un estudiante primero.");
    };

    if (concept === "Inscripción") {
      if (studentId && studentId !== "0") {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $("#subjectConceptDiv").prop("hidden", true);
          $("#careerDiv").prop("hidden", false);
          $("#careerName").val(escapeHtml(response.careerName));
          $("#careerName").prop("readonly", true);
        } else {
          infoAlert("El estudiante no tiene un grupo asignado.");
        }
      } else {
        ensureStudentSelected();
      }
    } else if (concept === "Mensualidad") {
      if (studentId && studentId !== "0") {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $("#subjectConceptDiv").prop("hidden", true);
          $("#careerDiv").prop("hidden", false);
          $("#careerName").val(escapeHtml(response.careerName));
          $("#careerName").prop("readonly", true);
        } else {
          infoAlert("El estudiante no tiene un grupo asignado.");
        }
      } else {
        ensureStudentSelected();
      }
    } else if (concept === "Examen Extraordinario") {
      if (studentId && studentId !== "0") {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $("#subjectConceptDiv").prop("hidden", false);
          $("#careerDiv").prop("hidden", false);
          $("#careerName").val(escapeHtml(response.careerName));
          $("#careerName").prop("readonly", true);
          getSubjectsList($("#subjectConcept"), response.careerId);
          $("#subjectConcept").prop("disabled", false);
        } else {
          infoAlert("El estudiante no tiene un grupo asignado.");
        }
      } else {
        ensureStudentSelected();
      }
    } else if (concept === "Constancia de Estudios") {
      if (studentId && studentId !== "0") {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $("#subjectConceptDiv").prop("hidden", true);
          $("#careerDiv").prop("hidden", false);
          $("#careerName").val(escapeHtml(response.careerName));
          $("#careerName").prop("readonly", true);
        } else {
          infoAlert("El estudiante no tiene un grupo asignado.");
        }
      } else {
        ensureStudentSelected();
      }
    } else {
      // $("#paymentMonth").val(0).trigger("change").prop("disabled", false);
      $("#subjectConceptDiv").prop("hidden", true);
      $("#subjectConcept").prop("disabled", true).val(null).trigger("change");
      $("#childSubjectName").prop("disabled", true);
      $("#careerDiv").prop("hidden", true);
      $("#careerName").prop("readonly", true).val("");
    }
  });

  // Validaciones
  validateForm(
    "#paymentsForm",
    {
      studentName: { required: true, valueNotEquals: "0" },
      paymentDate: {
        required: function () {
          return !$("#todayDate").prop("checked");
        },
        date: true,
      },
      paymentConcept: { required: true, valueNotEquals: "0" },
      paymentMonth: { required: true, valueNotEquals: "0" },
      paymentPrice: { required: true, number: true, min: 0.01 },
      paymentTotal: { required: true, number: true, min: 0.01 },
      paymentMethod: { required: true, valueNotEquals: "0" },
      paymentInvoice: { required: true, valueNotEquals: " " },
    },
    {
      studentName: {
        required: "Por favor, seleccione un estudiante.",
        valueNotEquals: "Por favor, seleccione un estudiante.",
      },
      paymentDate: {
        required: "Por favor, ingrese una fecha.",
        date: "Por favor, ingrese una fecha válida.",
      },
      paymentConcept: {
        required: "Por favor, seleccione un concepto.",
        valueNotEquals: "Por favor, seleccione un concepto.",
      },
      paymentMonth: {
        required: "Por favor, seleccione un mes.",
        valueNotEquals: "Por favor, seleccione un mes.",
      },
      paymentPrice: {
        required: "Por favor, ingrese un monto.",
        number: "Por favor, ingrese un número válido.",
        min: "El monto debe ser mayor a 0.",
      },
      paymentTotal: {
        required: "Por favor, ingrese un total.",
        number: "Por favor, ingrese un número válido.",
        min: "El total debe ser mayor a 0.",
      },
      paymentMethod: {
        required: "Por favor, seleccione un método de pago.",
        valueNotEquals: "Por favor, seleccione un método de pago.",
      },
      paymentInvoice: {
        required: "Por favor, seleccione un tipo de comprobante.",
        valueNotEquals: "Por favor, seleccione un tipo de comprobante.",
      },
    },
  );

  // Carga inicial
  if ($("#studentName").length) {
    getStudentsNames();
  }

  observePaymentChanges();

  toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
  toggleInputOnCheckboxChange($("#paymentExtraCkeck"), $("#paymentExtra"));
  $("#paymentTotal").val("0.00");

  // Datepicker
  $("#paymentDate").datepicker({
    dateFormat: "yy-mm-dd",
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    language: "es",
  });

  // Eventos
  $("#paymentHistoryTable").on("click", ".sendPayment", async function () {
    const paymentId = $(this).data("id");
    const studentId = $(this).data("student");
    await sendPaymentByEmail(studentId, paymentId);
  });

  $("#paymentHistoryStudentTable").on(
    "click",
    ".sendPayment",
    async function () {
      const paymentId = $(this).data("id");
      const studentId = $(this).data("student");
      await sendPaymentByEmail(studentId, paymentId);
    },
  );

  $("#paymentMethod, #paymentExtra, #paymentPrice, #paymentExtraCkeck").on(
    "input change blur",
    CalculateTotal,
  );

  $("#paymentExtraCkeck").on("change", function () {
    const isChecked = $(this).prop("checked");
    toggleInput("#paymentExtra", isChecked, "0.00");
    CalculateTotal();
  });

  $("#todayDate").on("change", function () {
    toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
  });

  $("#paymentsForm").on("submit", function (e) {
    e.preventDefault();
    const data = $(this).serialize();
    if ($(this).valid()) {
      AddPayment(data);
    } else {
      infoAlert("Por favor, complete todos los campos requeridos.");
    }
  });

  $("#studentPaymentTable").on("click", ".viewPayments", async function () {
    const studentId = $(this).data("id");
    const studentName = $(this).data("name");

    $("#studentPaymentsModalLabel").html(
      `Historial de pagos del alumno: <strong>${studentName}</strong>`,
    );

    $("#studentPaymentsModal").data("student-name", studentName);
    $("#studentPaymentsModal").modal("show");

    $("#studentPaymentsModal").one("shown.bs.modal", async function () {
      await VerifyPaymentsForStudent(null, studentId, "view");
    });
  });

  $("#paymentHistoryStudentTable").on(
    "click",
    ".editStudentPayment",
    async function () {
      const paymentId = $(this).data("id");
      const studentId = $(this).data("student-id");
      const paymentDate = $(this).data("payment-date");

      const studentName =
        $("#studentPaymentsModal").data("student-name") || "Alumno";

      const formattedDate = formatFullDate(paymentDate);

      $("#studentPaymentEditModalLabel").html(
        `Editar pago de <strong>${studentName}</strong> del día <strong>${formattedDate}</strong>`,
      );
      $("#studentPaymentEditModal").modal("show");

      await VerifyPaymentsForStudent(paymentId, studentId, "edit");
    },
  );

  $("#updatePaymentStudent").on("submit", async function (e) {
    e.preventDefault();

    const data = $(this).serialize();

    if ($(this).valid()) {
      await UpdatePayment(data);
    } else {
      infoAlert("Por favor, complete todos los campos requeridos.");
    }
  });

  $("#paymentHistoryStudentTable").on(
    "click",
    ".deleteStudentPayment",
    async function () {
      const paymentId = $(this).data("id");

      // obtener instancia del modal
      const paymentsModalEl = document.getElementById("studentPaymentsModal");

      const paymentsModal = bootstrap.Modal.getInstance(paymentsModalEl);

      // cerrar modal antes del swal
      paymentsModal.hide();

      const result = await Swal.fire({
        title: "Eliminar pago",
        text: "Ingresa tu contraseña para continuar",
        input: "password",
        inputPlaceholder: "Contraseña",
        inputAttributes: {
          autocapitalize: "off",
          autocorrect: "off",
        },
        showCancelButton: true,
        confirmButtonText: "Eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        allowOutsideClick: false,
        inputValidator: (value) => {
          if (!value) {
            return "Debes ingresar tu contraseña";
          }
        },
      });

      // si cancela -> reabrir modal
      if (!result.isConfirmed) {
        paymentsModal.show();

        return;
      }

      try {
        loadingAlert();

        const response = await requestJson(phpPath, "DeletePayment", {
          paymentId,
          password: result.value,
        });

        if (response.success) {
          successAlert(response.message);

          $("#paymentHistoryStudentTable").DataTable().ajax.reload(null, false);
        } else {
          errorAlert(response.message);
        }
      } catch (error) {
        errorAlert("Error al eliminar el pago");
      } finally {
        // volver a abrir modal
        paymentsModal.show();
      }
    },
  );

  let suppressPaymentsModalReopen = false;

  $("#studentPaymentEditModal").on("hidden.bs.modal", function () {
    if (suppressPaymentsModalReopen) return;
    $("#studentPaymentsModal").modal("show");
  });

  $("#cancelReceipt").on("click", async function () {
    const paymentId = $("#idPayment").val();

    const editModalEl = document.getElementById("studentPaymentEditModal");
    const editModal = bootstrap.Modal.getInstance(editModalEl);

    suppressPaymentsModalReopen = true;
    editModal.hide();

    await new Promise((resolve) =>
      $(editModalEl).one("hidden.bs.modal", resolve),
    );

    const result = await Swal.fire({
      title: "Cancelar recibo",
      text: "Ingresa tu contraseña para continuar",
      input: "password",
      inputPlaceholder: "Contraseña",
      inputAttributes: { autocapitalize: "off", autocorrect: "off" },
      showCancelButton: true,
      confirmButtonText: "Continuar",
      cancelButtonText: "Cancelar",
      confirmButtonColor: "#d33",
      allowOutsideClick: false,
      inputValidator: (value) => {
        if (!value) return "Debes ingresar tu contraseña";
      },
    });

    if (!result.isConfirmed) {
      suppressPaymentsModalReopen = false;
      editModal.show();
      return;
    }

    try {
      loadingAlert();

      const authResponse = await requestJson(phpPath, "VerifyPassword", {
        password: result.value,
      });

      Swal.close();

      if (!authResponse.success) {
        errorAlert(authResponse.message || "Contraseña incorrecta.");
        suppressPaymentsModalReopen = false;
        editModal.show();
        return;
      }

      const cancelModalEl = document.getElementById("cancelReceiptModal");
      const cancelModal = new bootstrap.Modal(cancelModalEl);

      $("#cancelReason").val("");
      $("#cancelReasonError").addClass("d-none");
      cancelModal.show();

      $("#cancelReasonBack")
        .off("click")
        .on("click", () => {
          cancelModal.hide();
          suppressPaymentsModalReopen = false;
          editModal.show();
        });

      $("#confirmCancelReceipt")
        .off("click")
        .on("click", async () => {
          const reason = $("#cancelReason").val().trim();

          if (!reason) {
            $("#cancelReasonError").removeClass("d-none");
            return;
          }

          $("#cancelReasonError").addClass("d-none");
          cancelModal.hide();
          loadingAlert();

          try {
            const response = await requestJson(phpPath, "CancelPayment", {
              paymentId,
              comments: reason,
            });

            if (response.success) {
              successAlert(response.message);
              $("#cancelReceipt").prop("hidden", true);
              $("#savePaymentChanges").prop("hidden", true);
              $("#paymentHistoryStudentTable")
                .DataTable()
                .ajax.reload(null, false);
            } else {
              errorAlert(response.message);
            }
          } catch {
            errorAlert("Error al cancelar el recibo.");
          } finally {
            suppressPaymentsModalReopen = false;
            editModal.show();
          }
        });
    } catch {
      Swal.close();
      errorAlert("Error inesperado.");
      suppressPaymentsModalReopen = false;
      editModal.show();
    }
  });

  // Botones guardar/actualizar día de pago (unificado)
  $("#savePaymentDays").on("click", () => handlePaymentDayAction("save"));
  $("#updatePaymentDays").on("click", () => handlePaymentDayAction("update"));
});

// =======================
// Exposed helpers que el código original usa en otros puntos
// =======================
const resetPaymentDaysForm = () => {
  try {
    $("#studentPaymentDate")[0]?.reset();
    $("#paymentDaysCard").prop("hidden", true);
  } catch {}
};

// =======================
// Formateo de fechas para mostrar en modales (ej. "Lunes 1 de Enero del 2024")
// =======================
const formatFullDate = (dateString) => {
  if (!dateString) return "Fecha inválida";

  const date = new Date(dateString);

  let formatted = new Intl.DateTimeFormat("es-MX", {
    weekday: "long",
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(date);

  formatted = formatted.replace(/ de (\d{4})$/, " del $1");

  return capitalizeFirstLetter(formatted);
};

// =======================
// Normaliza números para comparar "100" === "100.00" correctamente
// =======================
const normalizeValue = (val) => {
  const num = parseFloat(val);
  return isNaN(num) ? (val ?? "").trim() : String(num);
};

// =======================
// Helper para obtener datos actuales del formulario de edición (para snapshot/compare)
// =======================
const getCurrentPaymentFormData = () => ({
  price: normalizeValue($("#paymentPrice").val()),
  extra: normalizeValue($("#paymentExtra").val()),
  total: normalizeValue($("#paymentTotal").val()),
  method: $("#paymentMethod").val()?.trim(),
  comments: $("#paymentComments").val()?.trim(),
});
