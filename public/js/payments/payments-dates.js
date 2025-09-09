import { enviarPeticionAjaxAction } from "../utils/ajax.js"
import { successAlert, errorAlert } from "../utils/alerts.js"
import { initializeDataTable } from "../datatables/main.js"

let phpPath = '/backend/payments/routes.php';

const SetCashFormat = (value) => {
    return value.toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
}

function CashFormatInput(input) {
    return parseFloat(input.replace(/,/g, ''));
}

const SendPaymentsMount = async (data) => {
    console.log(data);
    enviarPeticionAjaxAction(phpPath, 'POST', 'setStudentPayMount', data)
    .done(function(data){
        if(data.success){
            successAlert(data.message);
            $('#mountModal').modal('hide');
            $('#studentPayTable').DataTable().ajax.reload();
            $("#monthAmountForm").trigger("reset");
        }else{
            errorAlert(data.message);
        }
    });
}

$(function() {
    $('#studentPayTable').on('input', '#studentAmount', function(){
        let value = $(this).val();
        let cash = CashFormatInput(value);
        $(this).val(SetCashFormat(cash));
    });

    initializeDataTable('#studentPayTable', phpPath, { action: 'getStudentsPayMount' }, [
        { data: 'id', 'className': 'text-center' },
        { data: 'name', 'className': 'text-center' },
        { data: null, 'className': 'text-center',
            render: function(data, type, row){
                if(row.amount == null){
                    return '<strong>No asignado</strong><br><button data-id="'+row.id+'" class="btn btn-primary btn-circle assignMount" data-bs-toggle="modal" data-bs-target="#mountModal"><i class="bi bi-plus "></i></button>';
                }else{
                    return '$'+row.amount;
                }
            }
         },
        {
            "data": null,
            "render": function(data, type, row) {
                return '<button disabled data-id="'+row.id+'" class="btn btn-primary btn-circle editStudent" data-bs-toggle="modal" data-bs-target="#StutentEditModal"><i class="bi bi-pencil-square"></i></button><button disabled id="deleteStudent" data-id="'+row.studentId+'" class="btn btn-danger btn-circle"><i class="bi bi-trash-fill"></i></button>';
            
            },
            "className": "text-center"
        }
    ]);

    $("#monthAmountForm").on("submit", function(e){
        e.preventDefault();

        let data = { amount: CashFormatInput($("#studentAmount").val()), studentId: $(".assignMount").data("id") };

        SendPaymentsMount(data);
    });

    $('#mountModal').on('hidden.bs.modal', function () {
        $("#monthAmountForm").trigger("reset");
    });
});