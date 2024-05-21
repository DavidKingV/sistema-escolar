import { FillTable, ClearInputsEditEstudents } from './forms.js';
import { initializeStudentDataTable, initializeStudentsUsersTable } from '../datatables/index.js';

initializeStudentDataTable();
initializeStudentsUsersTable();

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

$("#updateStudent").on( "submit", function( event ) {
    event.preventDefault();
    const studentData = $(this).serialize();
     Swal.fire({
        title: '¿Estás seguro de actualizar los datos del estudiante?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateStudent(studentData);
            $('#StutentEditModal').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });
});

$("#addStudents").on( "submit", function( event ) {
    console.log("entro");
    event.preventDefault();
    const studentData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar el estudiante?',
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddStudent(studentData);
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });
});

$("#studentsUsersTable").on( "click", ".addUserStudents", function() {
    let studentId = $(this).data('id');
    let studentName = $(this).data('name');
    if(studentId && studentName){
        $("#studentUserId").val(studentId);
        $("#studentUserName").val(studentName);
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para asignar un usuario.'
        });
    }
});

/*$("#studentUserAdd").on("blur", function(){
    let studentUserAdd = $(this).val();
    if(studentUserAdd){
        setTimeout(() => {
            VerifyUser(studentUserAdd);
        }, 1000);
    }else{
        $("#studentUserAdd-error").text('Por favor, ingresa un usuario.');
    }
});*/

export const VerifyUser = async (studentUserAdd) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentUserAdd: studentUserAdd, action: 'verifyStudentUser'}
        });
        if(response.success){
            return !response.user;
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al verificar el usuario',
                text: response.message
            });
            return false;
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al verificar el usuario',
            text: 'Ocurrió un error al verificar el usuario, por favor intenta de nuevo más tarde.'
        });
        return false;
    }
}

const GetStudentsData = async (studentId) => {

    try {
        const response = await $.ajax({
            url: 'php/students/routes.php',
            type: 'GET',
            data: {studentId: studentId, action: 'getStudentData'}
            
        });
        if(response.success){
            // Fill the table with the data
            FillTable(response);
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al obtener los datos',
                text: response.message,
                confirmButtonText: 'Inciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'index.html';
                }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos',
            text: 'Ocurrió un error al obtener los datos del servidor, por favor intenta de nuevo más tarde.'
        });
    }

}

const AddStudent = async (studentData) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentData: studentData, action: 'addStudent'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Estudiante agregado',
                text: response.message
            });
            // Reload the table
            $('#addStudents')[0].reset();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar el estudiante',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el estudiante',
            text: 'Ocurrió un error al agregar el estudiante, por favor intenta de nuevo más tarde.'
        });
    }
}

const UpdateStudent = async (studentData) => {
    console.log(studentData);
    try {
        const response = await $.ajax({
            url: 'php/students/routes.php',
            type: 'POST',
            data: {studentData: studentData, action: 'updateStudent'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Estudiante actualizado',
                text: response.message
            });
            // Reload the table
            $('#studentTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar el estudiante',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar el estudiante',
            text: 'Ocurrió un error al actualizar el estudiante, por favor intenta de nuevo más tarde.'
        });
    }
}

const DeleteStudent = async (studentId) => {
    try {
        const response = await $.ajax({
            url: 'php/students/routes.php',
            type: 'POST',
            data: {studentId: studentId, action: 'deleteStudent'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Estudiante eliminado',
                text: response.message
            });
            // Reload the table
            $('#studentTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar el estudiante',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el estudiante',
            text: 'Ocurrió un error al eliminar el estudiante, por favor intenta de nuevo más tarde.'
        });
    }
}





//al cerra el modal limpiar los inputs
$('#StutentEditModal').on('hidden.bs.modal', function() {
    ClearInputsEditEstudents();
});
