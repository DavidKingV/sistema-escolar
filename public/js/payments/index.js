
/**
 * index.optimized.js
 * Optimizado para rendimiento, legibilidad y seguridad.
 * - Peticiones centralizadas con manejo de errores.
 * - Sanitización básica de entradas/salidas.
 * - Prevención de XSS en renderers.
 * - Eliminación de redundancias y logs sensibles.
 */

import { enviarPeticionAjaxAction } from '../utils/ajax.js';
import { sendFetch } from '../utils/fetch.js';
import { successAlert, errorAlert, loadingAlert, infoAlert } from '../utils/alerts.js';
import { validateForm, capitalizeFirstLetter } from '../global/validate/index.js';
import { initializeDataTable } from '../global/dataTables.js';
import { sendNewPayment } from './newPayment.js';

// =======================
// Config
// =======================
const phpPath = '../../backend/payments/routes.php';
const studentsPath = '../../backend/students/routes.php';
const apiPath = '../api.php';
const DEBUG = false; // Cambia a true solo durante desarrollo

// =======================
// Utils (seguridad y helpers)
// =======================
const log = (...args) => { if (DEBUG) console.log('[DEBUG]', ...args); };

const escapeHtml = (str) => {
  return String(str)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;')
    .replaceAll("'", '&#039;');
};

const sanitize = (v) => typeof v === 'string' ? v.replace(/[<>]/g, '') : v;

const isValidId = (id) => /^\d+$/.test(String(id));
const isValidDay = (day) => Number.isFinite(+day) && +day >= 1 && +day <= 31;

const toMysqlDateWithDay = (day) => {
  const d = new Date();
  d.setHours(0,0,0,0);
  d.setDate(Number(day));
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
};

const showLoader = () => $("#globalLoader").fadeIn(200);
const hideLoader = () => $("#globalLoader").fadeOut(200);

// Petición centralizada con sendFetch (mantiene cookies/CSRF definidos en tu helper)
const requestJson = async (url, action, payload = {}) => {
  try {
    const res = await sendFetch(url, 'POST', { action, ...payload });
    const data = await res.json();
    if (!data || typeof data !== 'object') throw new Error('Respuesta inválida del servidor');
    return data;
  } catch (err) {
    log('requestJson error:', err);
    throw err;
  }
};

// =======================
// Flujo: Estudiantes
// =======================
const getStudentsNames = async () => {
  try {
    showLoader();
    const students = await requestJson(studentsPath, 'GetStudentsNames');

    if (!Array.isArray(students) || students.length === 0) return;

    const $select = $('#studentName');
    const fragment = $(document.createDocumentFragment());

    students.forEach((student) => {
      if (student && student.success !== false) {
        fragment.append(new Option(escapeHtml(student.name), String(student.id)));
      }
    });

    $select.append(fragment).select2({ theme: 'bootstrap-5' });

    // Cuando se elija un alumno, verificar si tiene configuración de pago mensual
    $select.on('change', async function () {
      const studentId = $(this).val();
      if (isValidId(studentId)) {
        await VerifyMonthlyPayment(studentId);
      } else {
        infoAlert('Estudiante inválido.');
      }
    });
  } catch (error) {
    errorAlert('No se pudieron cargar los estudiantes.');
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
    const resp = await enviarPeticionAjaxAction(phpPath, 'POST', 'AddPayment', serializedForm);
    const response = typeof resp === 'string' ? JSON.parse(resp) : resp;

    if (response?.success) {
      $("#paymentsForm")[0].reset();
      $("#studentName").val('0').trigger('change');
      $("#paymentHistoryTable").DataTable().destroy();
      $("#subjectConceptDiv").prop('hidden', true);
      $("#subjectConcept").prop("disabled", true).val(null).trigger('change');
      $("#childSubjectName").prop("disabled", true);
      $("#careerName").prop("disabled", true).val('');
      $("#childSubjectDiv").prop('hidden', true);
      $("#careerDiv").prop('hidden', true);
      hideLoadedContent();

      const dataObj = Object.fromEntries(new URLSearchParams(serializedForm));

      if (dataObj.toEmail === 'on') {
        try {
          const sent = await sendNewPayment(sanitize(dataObj.studentName), response.paymentId);
          if (sent) {
            successAlert('Pago registrado y comprobante enviado correctamente.');
          } else {
            infoAlert('Pago registrado, pero no se pudo enviar el comprobante.');
          }
        } catch (e) {
          infoAlert('Pago registrado, pero falló el envío del comprobante.');
        }
      } else {
        successAlert(escapeHtml(response.message || 'Pago registrado.'));
      }
    } else {
      errorAlert(escapeHtml(response?.message || 'No se pudo registrar el pago.'));
    }
  } catch (err) {
    errorAlert('Error inesperado al registrar el pago.');
  }
};

const VerifyMonthlyPayment = async (studentId) => {
  try {
    loadingAlert();

    if (!isValidId(studentId)) {
      infoAlert('Estudiante inválido.');
      resetPaymentDaysForm();
      return;
    }

    const response = await requestJson(phpPath, 'VerifyMonthlyPayment', { studentId });

    if (!response.success) {
      infoAlert(escapeHtml(response.message || 'No hay configuración de pago.'));
      resetPaymentDaysForm();
      return;
    }

    $("#studentId").val(studentId);
    $("#paymentDaysCard").prop('hidden', false);
    $("#savePaymentDays").prop('disabled', true);
    $("#updatePaymentDays").prop('disabled', false);

    $("#paymentDay").val(sanitize(response.payment_day));
    $("#paymentConceptDay").val(sanitize(response.concept)).trigger('change');
    $("#paymentAmountDay").val(sanitize(response.monthly_amount));

    const currentMonth = new Date().toLocaleString('default', { month: 'long' });
    $("#paymentMonth").val(capitalizeFirstLetter(currentMonth)).trigger('change');
    changeCurrentMonth();

    $("#paymentPrice").val(sanitize(response.monthly_amount));
    CalculateTotal();

    // Inicializar DataTable con renderers seguros
    initializeDataTable(
      '#paymentHistoryTable',
      phpPath,
      { action: 'GetPaymentHistory', studentId },
      [
        { data: 'concept', className: 'text-center', render: (d) => escapeHtml(d) },
        { data: 'amount', className: 'text-center', render: (d) => $.fn.dataTable.render.number(',', '.', 2, '$').display(d) },
        { data: 'payment_date', className: 'text-center', render: (d) => escapeHtml(d) },
        {
          data: null,
          className: 'text-center',
          orderable: false,
          render: (_, __, row) => {
            const pid = escapeHtml(String(row.id ?? ''));
            const sid = escapeHtml(String(row.id_student ?? ''));
            return `<button class="btn btn-sm btn-primary sendPayment" data-id="${pid}" data-student="${sid}">Enviar</button>`;
          }
        }
      ]
    );

    showLoadedContent();
    await SurchargeForLatePayment(response.payment_day, studentId);
  } catch (error) {
    errorAlert('Ocurrió un error inesperado al verificar el pago mensual.');
  } finally {
    //Swal.close?.();
  }
};

const sendPaymentByEmail = async (studentId, paymentId) => {
  try {
    if (!isValidId(studentId) || !isValidId(paymentId)) {
      return errorAlert('Identificador inválido');
    }
    loadingAlert();
    const response = await requestJson(phpPath, 'SendPaymentByEmail', { studentId, paymentId });
    response.success ? successAlert(escapeHtml(response.message)) : errorAlert(escapeHtml(response.message));
  } catch {
    errorAlert('Ocurrió un error inesperado al enviar el comprobante');
  } finally {
    Swal.close?.();
  }
};

const toggleInputOnCheckboxChange = (check, input) => {
  const isChecked = check.prop('checked');
  input.prop('disabled', isChecked);
  if (isChecked) input.val('');
};

const CalculateTotal = () => {
  const price = parseFloat($("#paymentPrice").val()) || 0;
  const $extra = $("#paymentExtra");
  const extra = ($extra.length && !$extra.prop('disabled')) ? (parseFloat($extra.val()) || 0) : 0;
  const total = (price + extra);
  $("#paymentTotal").val(total.toFixed(2));
};

const toggleInput = (selector, disabled, value = null) => {
  $(selector).prop('disabled', disabled);
  if (value !== null) $(selector).val(value);
};

const SurchargeForLatePayment = async (paymentDay, studentId) => {
  try {
    const mysqlDate = toMysqlDateWithDay(paymentDay);
    const payload = { data: { studentId, paymentDay: mysqlDate } };
    const response = await requestJson(phpPath, 'CheckIfPaymentMade', payload);

    if (!response.success) {
      return infoAlert(escapeHtml(response.message || 'No se pudo verificar el recargo.'));
    }

    const messages = {
      ON_TIME: 'El pago de este mes ya ha sido realizado a tiempo.',
      EXTEMPORANEO: 'El pago de este mes ya ha sido realizado, pero fue tarde. Se aplicó un recargo por pago tardío.',
      PENDING: (day) => `El pago de este mes aún no se ha realizado. Si se paga después del día ${day ?? 'SIN DEFINIR'}, se aplicará un recargo.`
    };

    const msg = typeof messages[response.data?.status] === 'function'
      ? messages[response.data.status](paymentDay)
      : messages[response.data?.status] || 'Estado no reconocido.';

    infoAlert(msg);
  } catch {
    infoAlert('No se pudo verificar el recargo por pago tardío.');
  }
};

const changeCurrentMonth = () => {
  let currentMonth = new Date().toLocaleString('default', { month: 'long' });
  currentMonth = capitalizeFirstLetter(currentMonth);
  if (currentMonth !== $("#paymentMonth").val()) {
    $("#todayDate").prop('checked', false);
    $("#paymentDate").prop('disabled', false);
  } else {
    $("#todayDate").prop('checked', true);
    $("#paymentDate").prop('disabled', true);
  }
};

const handlePaymentDayAction = async (actionType) => {
  const studentId = $("#studentId").val();
  const paymentDay = $("#paymentDay").val();
  const paymentConcept = $("#paymentConceptDay").val();
  const paymentAmount = $("#paymentAmountDay").val();

  if (!isValidId(studentId)) return errorAlert('Estudiante inválido.');
  if (!isValidDay(Number(paymentDay))) return errorAlert('Por favor, ingrese un día válido (1-31).');

  const payload = {
    data: {
      studentId: String(studentId),
      paymentDay: String(paymentDay),
      paymentConcept: sanitize(paymentConcept),
      paymentAmount: String(paymentAmount)
    }
  };

  loadingAlert();
  try {
    const resp = await requestJson(phpPath, 'savePaymentDays', payload);
    if (resp.success) {
      successAlert(escapeHtml(resp.message));
      if (actionType === 'save') {
        $("#studentPaymentDate")[0]?.reset();
        hideLoadedContent();
      }
    } else {
      errorAlert(escapeHtml(resp.message || `Error al ${actionType === 'save' ? 'reservar' : 'actualizar'}`));
    }
  } catch (err) {
    errorAlert('Error de red al procesar la solicitud.');
  } finally {
    Swal.close?.();
  }
};

// =======================
// API académica (grupos/materias)
// =======================
const getGroupCareer = async (studentId) => {
  try {
    if (!isValidId(studentId)) throw new Error('ID inválido');
    return await requestJson(apiPath, 'getGroupCareer', { studentId });
  } catch (error) {
    log(error);
    return { success: false, message: 'No se pudo obtener el grupo/carrera.' };
  }
};

const GetChildSubject = async (subjectId) => {
  try {
    const subjectsList = await requestJson(phpPath, 'getChildSubject', { subjectId });
    if (!subjectsList || subjectsList.success === false || !subjectsList.length) {
      $("#childSubjectName").prop('disabled', true);
      return;
    }

    const $select = $('#childSubjectName');
    $select.empty();

    $.each(subjectsList, function (_index, subject) {
      if (subject?.success !== false) {
        $select.append(
          $('<option>', {
            value: String(subject.childSubjectName),
            text: escapeHtml(subject.childSubjectName)
          })
        );
      }
    });

    $select.select2({
      theme: 'bootstrap-5',
      placeholder: 'Selecciona la submateria'
    });

    $("#childSubjectDiv").prop('hidden', false);
    $("#childSubjectName").prop('disabled', false);
  } catch (error) {
    errorAlert('Error al cargar submaterias.');
  }
};

const getSubjectsList = async (input, careerId) => {
  try {
    input.select2({
      theme: 'bootstrap-5',
      placeholder: 'Selecciona una materia',
      ajax: {
        url: apiPath,
        type: 'POST',
        dataType: 'json',
        delay: 250,
        data: params => ({
          action: 'getSubjectsListSelect',
          careerId,
          search: params.term,
          page: params.page || 1
        }),
        processResults: (data, params) => {
          params.page = params.page || 1;

          const results = data.results.map(item => ({
                id: item.text,  // 👈 usar el nombre como value
                text: item.text // 👈 mostrar también el nombre
            }));

          return { results, pagination: data.pagination };
        },
        cache: true
      },
      minimumInputLength: 2, // evitar querys vacías
      language: {
        inputTooShort: () => 'Por favor ingrese al menos 2 caracteres',
        searching: () => 'Buscando...',
        noResults: () => 'No se encontraron resultados.'
      }
    });

    input.on('select2:select', (e) => GetChildSubject(e.params.data.id));

    const resetChild = () => {
      $('#childSubjectName').empty().append('<option selected value="">Submateria</option>').prop('disabled', true);
    };
    input.on('select2:unselect', resetChild);
    input.on('select2:clear', resetChild);
    input.on('change', resetChild);
  } catch (error) {
    errorAlert('Error al inicializar la búsqueda de materias.');
  }
};

// =======================
// DOM Ready
// =======================
$(document).ready(function () {
  // Conceptos que abren selects dinámicos
  $("#paymentConcept").on('change', async function () {
    const concept = $(this).val();
    const studentId = $('#studentName').val();

    const ensureStudentSelected = () => {
      $('#subjectConceptDiv').prop('hidden', true);
      $("#subjectConcept").prop("disabled", true).val(null).trigger('change');
      $("#childSubjectName").prop("disabled", true);
      $('#careerName').prop("disabled", true);
      $('#paymentConcept').val(0).trigger('change');
      infoAlert('Por favor, seleccione un estudiante primero.');
    };

    if (concept === 'Examen Extraordinario') {
      if (studentId && studentId !== '0') {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $('#subjectConceptDiv').prop('hidden', false);
          getSubjectsList($('#subjectConcept'), response.careerId);
          $("#subjectConcept").prop("disabled", false)
        } else {
          infoAlert('El estudiante no tiene un grupo asignado.');
        }
      } else {
        ensureStudentSelected();
      }
    } else if (concept === 'Constancia de Estudios') {
      if (studentId && studentId !== '0') {
        loadingAlert();
        const response = await getGroupCareer(studentId);
        Swal.close?.();
        if (response.success) {
          $('#careerDiv').prop('hidden', false);
          $("#careerName").val(escapeHtml(response.careerName));
        } else {
          infoAlert('El estudiante no tiene un grupo asignado.');
        }
      } else {
        ensureStudentSelected();
      }
    } else {
      $('#subjectConceptDiv').prop('hidden', true);
      $("#subjectConcept").prop("disabled", true).val(null).trigger('change');
      $("#childSubjectName").prop("disabled", true);
      $('#careerName').prop("disabled", true);
      $('#subjectConcept').val(null).trigger('change');
    }
  });

  // Validaciones
  validateForm("#paymentsForm", {
    studentName: { required: true, valueNotEquals: "0" },
    paymentDate: {
      required: function () { return !$("#todayDate").prop('checked'); },
      date: true
    },
    paymentConcept: { required: true, valueNotEquals: "0" },
    paymentMonth: { required: true, valueNotEquals: "0" },
    paymentPrice: { required: true, number: true, min: 0.01 },
    paymentTotal: { required: true, number: true, min: 0.01 },
    paymentMethod: { required: true, valueNotEquals: "0" },
    paymentInvoice: { required: true, valueNotEquals: " " }
  }, {
    studentName: { required: "Por favor, seleccione un estudiante.", valueNotEquals: "Por favor, seleccione un estudiante." },
    paymentDate: { required: "Por favor, ingrese una fecha.", date: "Por favor, ingrese una fecha válida." },
    paymentConcept: { required: "Por favor, seleccione un concepto.", valueNotEquals: "Por favor, seleccione un concepto." },
    paymentMonth: { required: "Por favor, seleccione un mes.", valueNotEquals: "Por favor, seleccione un mes." },
    paymentPrice: { required: "Por favor, ingrese un monto.", number: "Por favor, ingrese un número válido.", min: "El monto debe ser mayor a 0." },
    paymentTotal: { required: "Por favor, ingrese un total.", number: "Por favor, ingrese un número válido.", min: "El total debe ser mayor a 0." },
    paymentMethod: { required: "Por favor, seleccione un método de pago.", valueNotEquals: "Por favor, seleccione un método de pago." },
    paymentInvoice: { required: "Por favor, seleccione un tipo de comprobante.", valueNotEquals: "Por favor, seleccione un tipo de comprobante." }
  });

  // Carga inicial
  getStudentsNames();
  toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
  toggleInputOnCheckboxChange($("#paymentExtraCkeck"), $("#paymentExtra"));
  $("#paymentTotal").val('0.00');

  // Datepicker
  $("#paymentDate").datepicker({
    dateFormat: 'yy-mm-dd',
    changeMonth: true,
    changeYear: true,
    showButtonPanel: true,
    language: 'es',
  });

  // Eventos
  $("#paymentHistoryTable").on('click', '.sendPayment', async function () {
    const paymentId = $(this).data('id');
    const studentId = $(this).data('student');
    await sendPaymentByEmail(studentId, paymentId);
  });

  $("#paymentMethod, #paymentExtra, #paymentPrice, #paymentExtraCkeck").on('input change blur', CalculateTotal);

  $("#paymentExtraCkeck").on('change', function () {
    const isChecked = $(this).prop('checked');
    toggleInput("#paymentExtra", isChecked, '0.00');
    CalculateTotal();
  });

  $("#todayDate").on('change', function () {
    toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
  });

  $("#paymentsForm").on('submit', function (e) {
    e.preventDefault();
    const data = $(this).serialize();
    if ($(this).valid()) {
      AddPayment(data);
    } else {
      infoAlert('Por favor, complete todos los campos requeridos.');
    }
  });

  $("#paymentMonth").on('change', changeCurrentMonth);

  // Botones guardar/actualizar día de pago (unificado)
  $("#savePaymentDays").on('click', () => handlePaymentDayAction('save'));
  $("#updatePaymentDays").on('click', () => handlePaymentDayAction('update'));
});

// =======================
// Exposed helpers que el código original usa en otros puntos
// =======================
const resetPaymentDaysForm = () => {
  try {
    $("#studentPaymentDate")[0]?.reset();
    $("#paymentDaysCard").prop('hidden', true);
  } catch {}
};
