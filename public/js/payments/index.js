import { enviarPeticionAjaxAction } from '../utils/ajax.js';
import { sendFetch } from '../utils/fetch.js';
import { successAlert, errorAlert, loadingAlert, infoAlert } from '../utils/alerts.js';
import { validateForm, capitalizeAllWords, capitalizeAll,capitalizeFirstLetter } from '../global/validate/index.js';
import  { initializeDataTable } from '../global/dataTables.js';
import { sendNewPayment } from './newPayment.js';

let phpPath = '../../backend/payments/routes.php';

const showLoader = () => {
  $("#globalLoader").fadeIn(200);
};

const hideLoader = () => {
  $("#globalLoader").fadeOut(200);
};

const getStudentsNames = async () => {

    const GetStudentsSelect = async () => {
        try {
            const response = await $.ajax({
                url: '../../backend/students/routes.php',
                type: 'POST',
                data: {action: 'GetStudentsNames'},
                dataType: 'json'
            });
            return response;
        } catch (error) {
            console.error('Error al obtener los datos:', error);
            throw new Error('Error al obtener los datos');
        }
    };

    try {
        showLoader(); // 👈 Mostrar loader

        const students = await GetStudentsSelect();

        if (!students || students.length === 0) {
            return;
        }

        const $select = $('#studentName'); // => const $select
        const fragment = $(document.createDocumentFragment());
        students.forEach(student => {
            if (student.success !== false) {
                fragment.append(new Option(student.name, student.id));
            }
        });
        $select.append(fragment).select2({ theme: "bootstrap-5" });        

        //cuando se elija un alumno, verificar si tiene datos fiscales
        $select.on('change', async function() {
            let studentId = $(this).val();
            await VerifyMonthlyPayment(studentId);
        });
    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } finally {
        hideLoader(); // 👈 Ocultar loader siempre (éxito o error)
    }
   
};

const AddPayment = async (data) => {
    loadingAlert();
    enviarPeticionAjaxAction(phpPath, 'POST', 'AddPayment', data)
    .done(function (response) {
        if (response.success) {
            $("#paymentsForm")[0].reset();
            $("#studentName").val('0').trigger('change');

            $("#paymentHistoryTable").DataTable().destroy();
            hideLoadedContent();

            let dataObj = Object.fromEntries(new URLSearchParams(data));   
            
            if(dataObj.toEmail === 'on'){
                const toSend = sendNewPayment(dataObj.studentName, response.paymentId);

                if(toSend){
                    successAlert('Pago registrado y comprobante enviado correctamente.');
                }else{
                    infoAlert('Pago registrado, pero no se pudo enviar el comprobante: ' + toSend.message);
                }

            }else{
                successAlert(response.message);
            }
 
        }else{
            errorAlert(response.message);
        }
    })
}

function showLoadedContent() {
    // Ocultar placeholders
    $("#paymentDaysPlaceholder").addClass("d-none");
    $("#paymentHistoryPlaceholder").addClass("d-none");

    // Mostrar contenido real
    $("#paymentDaysContent").removeClass("d-none");
    $("#paymentHistoryTable").removeClass("d-none"); // 👈 ahora muestra la tabla
}

const VerifyMonthlyPayment = async (studentId) => {
    try {
        loadingAlert();
        const res = await sendFetch(phpPath, 'POST', { action: 'VerifyMonthlyPayment', studentId });
        const response = await res.json();

        if (!response.success) {
            infoAlert(response.message);
            resetPaymentDaysForm();
            return;
        }

        $("#studentId").val(studentId);
        $("#paymentDaysCard").prop('hidden', false);
        $("#savePaymentDays").prop('disabled', true);
        $("#updatePaymentDays").prop('disabled', false);

        $("#paymentDay").val(response.payment_day);
        $("#paymentConceptDay").val(response.concept).trigger('change');
        $("#paymentAmountDay").val(response.monthly_amount);

        const currentMonth = new Date().toLocaleString('default', { month: 'long' });
        $("#paymentMonth").val(capitalizeFirstLetter(currentMonth)).trigger('change');

        changeCurrentMonth();

        $("#paymentPrice").val(response.monthly_amount);
        CalculateTotal();

        initializeDataTable('#paymentHistoryTable', phpPath, { action: 'GetPaymentHistory', studentId }, [
            { data: 'concept', className: 'text-center' },
            { data: 'amount', render: $.fn.dataTable.render.number(',', '.', 2, '$'), className: 'text-center' },
            { data: 'payment_date', className: 'text-center' }
        ]);

        showLoadedContent();
        await SurchargeForLatePayment(response.payment_day, studentId);

    } catch (error) {
        console.error('Error al verificar los datos:', error);
        errorAlert('Ocurrió un error inesperado');
    }
};

const toggleInputOnCheckboxChange = (check, input) => {
    const isChecked = check.prop('checked');
    input.prop('disabled', isChecked);
    if (isChecked) {
        input.val('');
    }

}

const CalculateTotal = () => {
    // Obtiene el valor del input "paymentAmount"
    let paymentAmount = parseFloat($("#paymentPrice").val()) || 0;

    // Inicializa paymentExtra en 0
    let $paymentExtraInput = $("#paymentExtra");
    let paymentExtra = parseFloat($paymentExtraInput.val()) || 0;

    // Verifica si el input "paymentExtra" existe y no está deshabilitado
    if ($paymentExtraInput.length && !$paymentExtraInput.prop('disabled')) {
        // Intenta obtener y convertir el valor del input a un número, o 0 si no es válido
        paymentExtra = parseFloat($paymentExtraInput.val()) || 0;
    }

    // Calcula el total
    let total = paymentAmount + paymentExtra;

    // Asigna el total al input "paymentTotal"
    $("#paymentTotal").val(total.toFixed(2));
}

const toggleInput = (selector, disabled, value = null) => {
    $(selector).prop('disabled', disabled);
    if (value !== null) $(selector).val(value);
};

const SurchargeForLatePayment = (paymentDay, studentId) => {

    const limitDate = new Date();
    limitDate.setDate(paymentDay);

    // Formatear a 'YYYY-MM-DD' para MySQL
    const yyyy = limitDate.getFullYear();
    const mm = String(limitDate.getMonth() + 1).padStart(2, '0');
    const dd = String(limitDate.getDate()).padStart(2, '0');
    const mysqlDate = `${yyyy}-${mm}-${dd}`;

    const payload = { studentId: studentId, paymentDay: mysqlDate };    

    sendFetch(phpPath, 'POST', { action: 'CheckIfPaymentMade', data: payload })
        .then(res => res.json())
        .then(response => {
            if (response.success) {
                // Mostrar mensaje basado en el estado del pago
                const messages = {
                    ON_TIME: 'El pago de este mes ya ha sido realizado a tiempo.',
                    EXTEMPORANEO: 'El pago de este mes ya ha sido realizado, pero fue tarde. Se aplicó un recargo por pago tardío.',
                    PENDING: (day) => `El pago de este mes aún no se ha realizado. Si se paga después del día ${day ?? 'SIN DEFINIR'}, se aplicará un recargo.`
                };

                infoAlert(typeof messages[response.data.status] === "function" ? messages[response.data.status](paymentDay) : messages[response.data.status]);
            }else{
                infoAlert(response.message);
            }
        });    
}

const changeCurrentMonth = () => {
    let currentMonth = new Date().toLocaleString('default', { month: 'long' });

    currentMonth = capitalizeFirstLetter(currentMonth);
    
    if(currentMonth !== $("#paymentMonth").val()){
        $("#todayDate").prop('checked', false);
        $("#paymentDate").prop('disabled', false);
    }else{
        $("#todayDate").prop('checked', true);
        $("#paymentDate").prop('disabled', true);
    }
}

const handlePaymentDayAction = async (actionType) => {
    const studentId = $("#studentId").val();
    const paymentDay = $("#paymentDay").val();
    
    // Validación centralizada
    if (!paymentDay || paymentDay < 1 || paymentDay > 31) {
        errorAlert('Por favor, ingrese un día válido (1-31).');
        return;
    }

    const payload = {
        studentId: studentId,
        paymentDay: paymentDay,
        paymentConcept: $("#paymentConceptDay").val(),
        paymentAmount: $("#paymentAmountDay").val()
    };

    loadingAlert();
    try {
        const res = await sendFetch(phpPath, 'POST', { action: 'savePaymentDays', data: payload });
        const resp = await res.json();

        if (resp.success) {
            successAlert(resp.message);
            // Si es un guardado nuevo, resetea el form
            if (actionType === 'save') {
                $("#studentPaymentDate")[0].reset();
                hideLoadedContent();
            }
        } else {
            errorAlert(resp.message || `Error al ${actionType === 'save' ? 'reservar' : 'actualizar'}`);
        }
    } catch (err) {
        console.error(err);
        errorAlert('Error de red al procesar la solicitud.');
    }
};

$("#paymentMethod, #paymentExtra, #paymentPrice, #paymentExtraCkeck").on('input change blur', function() {
        CalculateTotal();
    });

    $("#paymentExtraCkeck").on('change', function() {
        const isChecked = $(this).prop('checked');
        toggleInput("#paymentExtra", isChecked, '0.00');
        CalculateTotal();
    });

    $("#todayDate").on('change', function() {
        toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
    });

    $("#paymentsForm").on('submit', function(e) {
        e.preventDefault();
        let data = $(this).serialize();
        
        if($(this).valid()){
            AddPayment(data);
        }else{
            infoAlert('Por favor, complete todos los campos requeridos.');
        }

    });


    $("#paymentMonth").on('change', function() {
        changeCurrentMonth();
    });

    $("#savePaymentDays").on('click', () => handlePaymentDayAction('save'));
    $("#updatePaymentDays").on('click', () => handlePaymentDayAction('update'));

    $("#updatePaymentDays").on('click', async function() {
        let studentId = $("#studentId").val();
        let paymentDay = $("#paymentDay").val();
        let paymentConcept = $("#paymentConceptDay").val();
        let paymentAmount = $("#paymentAmountDay").val();
        let data = { studentId: studentId, paymentDay: paymentDay, paymentConcept: paymentConcept, paymentAmount: paymentAmount };
        if(paymentDay === '' || paymentDay < 1 || paymentDay > 31){
            errorAlert('Por favor, ingrese un día válido (1-31).');
            return;
        }
        loadingAlert();
        await sendFetch(phpPath, 'POST', { action: 'savePaymentDays', data: data })
        .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    successAlert(resp.message);
                } else {
                    errorAlert(resp.message || 'Error al actualizar');
                }
            })
            .catch(err => {
                console.error(err);
                errorAlert('Error de red');
            });

    });

function hideLoadedContent() {
    // Mostrar placeholders
    $("#paymentDaysPlaceholder").removeClass("d-none");
    $("#paymentHistoryPlaceholder").removeClass("d-none");

    // Ocultar contenido real
    $("#paymentDaysContent").addClass("d-none");
    $("#paymentHistoryTable").addClass("d-none"); // 👈 ocultamos la tabla completa
}

$(document).ready(function() {

    validateForm("#paymentsForm", {
        studentName: {
            required: true,
            valueNotEquals: "0"
        },
        paymentDate: {
            required: function() {
                return !$("#todayDate").prop('checked');
            },
            date: true
        },
        paymentConcept: {
            required: true,
            valueNotEquals: "0"
        },
        paymentMonth: {
            required: true,
            valueNotEquals: "0"
        },
        paymentPrice: {
            required: true,
            number: true,
            min: 0.01
        },
        paymentTotal: {
            required: true,
            number: true,
            min: 0.01
        },
        paymentMethod: {
            required: true,
            valueNotEquals: "0"
        },
        paymentInvoice: {
            required: true,
            valueNotEquals: " "
        }
    },{
        studentName: {
            required: "Por favor, seleccione un estudiante.",
            valueNotEquals: "Por favor, seleccione un estudiante."
        },
        paymentDate: {
            required: "Por favor, ingrese una fecha.",
            date: "Por favor, ingrese una fecha válida."
        },
        paymentConcept: {
            required: "Por favor, seleccione un concepto.",
            valueNotEquals: "Por favor, seleccione un concepto."
        },
        paymentMonth: {
            required: "Por favor, seleccione un mes.",
            valueNotEquals: "Por favor, seleccione un mes."
        },
        paymentPrice: {
            required: "Por favor, ingrese un monto.",
            number: "Por favor, ingrese un número válido.",
            min: "El monto debe ser mayor a 0."
        },
        paymentTotal: {
            required: "Por favor, ingrese un total.",
            number: "Por favor, ingrese un número válido.",
            min: "El total debe ser mayor a 0."
        },
        paymentMethod: {
            required: "Por favor, seleccione un método de pago.",
            valueNotEquals: "Por favor, seleccione un método de pago."
        },
        paymentInvoice: {
            required: "Por favor, seleccione un tipo de comprobante.",
            valueNotEquals: "Por favor, seleccione un tipo de comprobante."
        }
    });

    getStudentsNames();

    toggleInputOnCheckboxChange($("#todayDate"), $("#paymentDate"));
    toggleInputOnCheckboxChange($("#paymentExtraCkeck"), $("#paymentExtra"));

    $("#paymentTotal").val('0.00');
    
    $("#paymentDate").datepicker({
        dateFormat : 'yy-mm-dd',
        changeMonth: true,
        changeYear: true,
        showButtonPanel: true,
        language: 'es',

        onClose: function(dateText, inst) {
            $(this).val($.datepicker.formatDate('yy-mm-dd', new Date(dateText)));
        }
        
    });    

    
});