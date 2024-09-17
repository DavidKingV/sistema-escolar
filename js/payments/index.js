import { enviarPeticionAjaxAction } from '../utils/ajax.js';
import { successAlert, errorAlert, loadingAlert } from '../utils/alerts.js';

let phpPath = 'php/payments/routes.php';

const GetStudentsNames = async () => {

    const GetStudentsSelect = async () => {
        try {
            const response = await $.ajax({
                url: 'php/students/routes.php',
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
    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } 
   
};

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
    enviarPeticionAjaxAction(phpPath, 'POST', 'VerifyMonthlyPayment', studentId)
    .done(function (response) {
        if (response.success) {
            let monthlyPayment = response.monthly_amount;

            $("#paymentPrice").val(monthlyPayment);
            $("#paymentPrice").prop('readonly', true);

            CalculateTotal();

        }
    })
    .fail(function (error) {
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



$(document).ready(function() {
    GetStudentsNames();

    CheckActive($("#todayDate"), $("#paymentDate"));
    CheckActive($("#paymentExtraCkeck"), $("#paymentExtra"));
    
    $("#paymentInvoice").on('change', function() {
        let studentId = $("#studentName").val();
        let invoice = $(this).val();

        if(invoice === '1'){
            VerifyTaxData(studentId);
        }
    });

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
});