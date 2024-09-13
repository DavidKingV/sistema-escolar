import { enviarPeticionAjaxAction } from "../utils/ajax.js"
import { successAlert, errorAlert } from "../utils/alerts.js"
import { initializeDataTable } from "../datatables/main.js"

let phpPath = '../php/payments/routes.php';

$(function() {

    initializeDataTable('#studentPayTable', phpPath, { action: 'getStudentsPayMount' }, [
        { data: 'id', 'className': 'text-center' },
        { data: 'name', 'className': 'text-center' },
        { data: null, 'className': 'text-center',
            render: function(data, type, row){
                if(row.mount == null){
                    return '<strong>No asignado</strong><br><button data-id="'+row.id+'" class="btn btn-primary btn-circle assignMount" data-bs-toggle="modal" data-bs-target="#mountModal"><i class="bi bi-plus "></i></button>';
                }else{
                    return row.mount;
                }
            }
         },
        {
            "data": null,
            "render": function(data, type, row) {
                return '<button data-id="'+row.id+'" class="btn btn-primary btn-circle editStudent" data-bs-toggle="modal" data-bs-target="#StutentEditModal"><i class="bi bi-pencil-square"></i></button><button id="deleteStudent" data-id="'+row.studentId+'" class="btn btn-danger btn-circle"><i class="bi bi-trash-fill"></i></button>';
            
            },
            "className": "text-center"
        }
    ]);
});