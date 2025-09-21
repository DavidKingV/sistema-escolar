import { enviarPeticionAjaxAction } from '../utils/ajax.js';
import { sendFetch } from '../utils/fetch.js';
import { successAlert, errorAlert, loadingAlert, infoAlert } from '../utils/alerts.js';

let phpPath = '../../backend/payments/routes.php';

const GetStudentsNames = async () => {

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
        const students = await GetStudentsSelect();

        if (!students || students.length === 0) {
            return;
        }

        let $select = $('#studentName');
        $.each(students, function(index, student) {
            if (student.success !== false) {
                let $option = $('<option>', {
                    value: student.id,
                    text: student.name
                });

                $select.append($option);
            }
        });

        $select.select2({
            theme: "bootstrap-5"
        });

        //cuando se elija un alumno, verificar si tiene datos fiscales
        $select.on('change', async function() {
            let studentId = $(this).val();
            await VerifyMonthlyPayment(studentId);
        });
    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } 
   
};

const AddPaymentDays = async (data) => {
    enviarPeticionAjaxAction(phpPath, 'POST', 'AddPaymentDays', data)
    .done(function (response) {
        if (response.success) {
            successAlert(response.message);
            $("#studentPaymentDate")[0].reset();
            $("#paymentDaysCard").prop('hidden', true);
            $("#studentName").val('0').trigger('change');
        }else{
            errorAlert(response.message);
        }
    })
}

const AddPayment = async (data) => {
    enviarPeticionAjaxAction(phpPath, 'POST', 'AddPayment', data)
    .done(function (response) {
        if (response.success) {
            $("#paymentsForm")[0].reset();
            $("#studentName").val('0').trigger('change');
            successAlert(response.message);
        }else{
            errorAlert(response.message);
        }
    })
}

const HiddenOrShowDiv = (div, value) => {
    if (value === 'show') {
        $(div).prop('hidden', false).hide().slideDown();  // Mostrar con deslizamiento
    } else if (value === 'hide') {
        $(div).slideUp(function() {
            $(div).prop('hidden', true);  // Ocultar después del deslizamiento
        });
    }
};

const DisableOrEnableInput = (input, value) => {
    $(input).prop('disabled', value);
}

const VerifyTaxData = async (studentId) => {

    enviarPeticionAjaxAction(phpPath, 'POST', 'VerifyTaxData', studentId)
    .done(function (response) {
        if (response.success) {
            let fiscalId = $("#fiscalId");

            fiscalId.val(response.data.factuarapi_id);
            HiddenOrShowDiv("#invoiceInfoDiv", 'show');
            DisableOrEnableInput(fiscalId, false);

        }else{
            errorAlert(response.message);
            $("#paymentInvoice").val('');
         }
    })
    .fail(function (error) {
        console.error('Error al verificar los datos:', error);
    });
    
}

const VerifyMonthlyPayment = async (studentId) => {
    sendFetch(phpPath, 'POST', { action: 'VerifyMonthlyPayment', studentId: studentId })
    .then(res => res.json())
    .then(function (response) {
        console.log(response);
        if (response.success) {
            $("#studentId").val(studentId);
            $("#paymentDaysCard").prop('hidden', false);

            $("#savePaymentDays").prop('disabled', true);
            $("#updatePaymentDays").prop('disabled', false);

            $("#paymentDay").val(response.payment_day);
            $("#paymentConceptDay").val(response.concept).trigger('change');
            $("#paymentAmountDay").val(response.monthly_amount);

            // Establece el mes actual en el select "paymentMonth"
            const currentMonth = new Date().toLocaleString('default', { month: 'long' });
            const capitalizedMonth = currentMonth.charAt(0).toUpperCase() + currentMonth.slice(1);
            $("#paymentMonth").val(capitalizedMonth).trigger('change');

            const isLate = SurchargeForLatePayment(response.payment_day);
            if(isLate){
                infoAlert('El día de pago ha pasado. Se aplicará un recargo por pago tardío.');
                $("#paymentExtraCkeck").prop('checked', false);
                $("#paymentExtra").prop('disabled', false);
                $("#paymentExtra").val(200.00);
            }
            
        }else{
            infoAlert(response.message);
            $("#paymentDay").val('');
            $("#paymentConceptDay").val('Mensualidad').trigger('change');
            $("#paymentAmountDay").val('');
            $("#savePaymentDays").prop('disabled', false);
            $("#updatePaymentDays").prop('disabled', true);
        }
    })
    .catch(function (error) {
        console.error('Error al verificar los datos:', error);
    });
}

const CheckActive = (check, input) => {
    //verificar que check este activo
    if(check.prop('checked')){
        input.prop('disabled', true);
        input.val('');
    }else{
        input.prop('disabled', false);
    }

}

const CalculateTotal = () => {
    // Obtiene el valor del input "paymentAmount"
    let paymentAmount = parseFloat($("#paymentPrice").val()) || 0;

    // Inicializa paymentExtra en 0
    let paymentExtra = 0;
    let $paymentExtraInput = $("#paymentExtra");

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

const SurchargeForLatePayment = (paymentDay) => {
    const currentDay = new Date().getDate();
    if(paymentDay < currentDay){
        return true;
    }
    return false;
}

$(document).ready(function() {
    GetStudentsNames();

    CheckActive($("#todayDate"), $("#paymentDate"));
    CheckActive($("#paymentExtraCkeck"), $("#paymentExtra"));

    $("#paymentTotal").val('0.00');
    
    /*$("#paymentInvoice").on('change', function() {
        let studentId = $("#studentName").val();
        let invoice = $(this).val();

        if(invoice === '1'){
            VerifyTaxData(studentId);
        }
    });*/

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

    $("#paymentMethod, #paymentExtra, #paymentPrice, #paymentExtraCkeck").on('input change blur', function() {
        CalculateTotal();
    });

    $("#paymentExtraCkeck").on('change', function() {
        if($(this).prop('checked') || $(this).prop('unchecked')){
            $("#paymentExtra").val('0.00');
            CalculateTotal();
        }
    });

    $("#todayDate").on('change', function() {
        CheckActive($("#todayDate"), $("#paymentDate"));        
    });

    $("#paymentExtraCkeck").on('change', function() {
        CheckActive($("#paymentExtraCkeck"), $("#paymentExtra"));
    });

    $("#paymentsForm").on('submit', function(e) {
        e.preventDefault();
        let data = $(this).serialize();
        
        if($(this).valid()){
            AddPayment(data);
        }
    });

    $("#studentName").on('change', async function() {
        let studentId = $("#studentName").val();
        if($("#paymentConcept").val() === 'Mensualidad'){
            loadingAlert();
            await VerifyMonthlyPayment(studentId);
            Swal.close();
        }else{
            $("#paymentPrice").prop('readonly', false);
            $("#paymentPrice").val('');
            CalculateTotal();
        }
    });

    $("#paymentConcept").on('change', async function() {
        let studentId = $("#studentName").val();
        if($("#paymentConcept").val() === 'Mensualidad'){
            loadingAlert();
            await VerifyMonthlyPayment(studentId);
            Swal.close();    
        }else{
            $("#paymentPrice").prop('readonly', false);
            $("#paymentPrice").val('');
            CalculateTotal();
        }
    });

    $("#paymentDaysCard").prop('hidden', true);


    $("#savePaymentDays").on('click', async function() {
        const payload = {
            studentId: $("#studentId").val(),
            paymentDay: $("#paymentDay").val(),
            paymentConcept: $("#paymentConceptDay").val(),
            paymentAmount: $("#paymentAmountDay").val()
        }

        if(payload.paymentDay === '' || payload.paymentDay < 1 || payload.paymentDay > 31){
            errorAlert('Por favor, ingrese un día válido (1-31).');
            return;
        }

        loadingAlert();
        await sendFetch(phpPath, 'POST', { action: 'savePaymentDays', data: payload })
        .then(res => res.json())
            .then(resp => {
                if (resp.success) {
                    successAlert(resp.message);
                } else {
                    errorAlert(resp.message || 'Error al reservar');
                }
            })
            .catch(err => {
                console.error(err);
                errorAlert('Error de red');
            });
    });

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
});