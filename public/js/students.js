import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

const callback = '/api.php';

$(function() {
    
    initializeDataTable('#studentsTable', callback, { action: 'getStudentsList' }, [
        { "data": "no_control", "className": "text-center" },
        { "data": "name", "className": "text-center" },
        { "data": "phone", "className": "text-center" },
        { "data": "email", "className": "text-center" },
        { "data": "group_name", "className": "text-center", "defaultContent": "No asignado"},
        {"data": null, "render": function(data, type, row) {
            return '<button data-encode="'+row.encodeJWT+'" data-student="'+row.studentId+'" class="btn btn-primary btn-circle GradeStudent"><i class="bi bi-journal-check"></i></button>';
            },"className": "text-center"
        },
        {"data": null, "render": function(data, type, row) {
            return '<button data-id="'+row.studentId+'" class="btn btn-primary btn-circle editStudent" data-bs-toggle="modal" data-bs-target="#StutentEditModal"><i class="bi bi-pencil-square"></i></button><button id="deleteStudent" data-id="'+row.studentId+'" class="btn btn-danger btn-circle"><i class="bi bi-trash-fill"></i></button>';   
            },"className": "text-center"
        }
    ]);

});


$('#studentTable').on('click', '.editStudent', function() {
    // Get the student id
    const studentId = $(this).data('id');
    if (studentId) {
        // Get the student data
        GetStudentsData(studentId);
    } else {
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para editar.'
        });
    }
});

$('#studentTable').on('click', '#deleteStudent', function() {

    const studentId = $(this).data('id');
    if(studentId){
        Swal.fire({
            title: '¿Estás seguro de eliminar el estudiante?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                DeleteStudent(studentId);
            }
        });
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para eliminar.'
        });
    }
});

$("#studentTable").on("click", ".GradeStudent", function(){
    let studentId = $(this).data('student');
    let encodeJWT = $(this).data('encode');
    if(studentId){
        window.location.href = `alumnos/calificaciones.php?student=${studentId}&encode=${encodeJWT}`;
    }else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para asignar un usuario.'
        });
    }
});