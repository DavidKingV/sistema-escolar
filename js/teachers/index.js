import { initializeTeachersDataTable } from './../datatables/index.js';
import { FillTable, ClearInputsEditTeacher } from '../teachers/forms.js';

initializeTeachersDataTable();

$("#teachersTable").on("click", ".editTeacher", function() {
    let teacherId = $(this).data("id");

    if(teacherId){
        GetTeacherData(teacherId);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del profesor, por favor intenta de nuevo.'
        });
    
    }
});

$("#editTeacherForm").submit(function(e) {
    e.preventDefault();
    const teacherEditData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de actualizar los datos del profesor?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateTeacherData(teacherEditData);
            $('#TeacherEditModal').modal('hide');
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

$("#teachersTable").on("click", ".deleteTeacher", function() {
    let teacherId = $(this).data("id");
    if(teacherId){
        Swal.fire({
            title: '¿Estás seguro de eliminar al profesor?',
            text: '¡No podrás revertir esto!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                DeleteTeacher(teacherId);
            }
        });
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del profesor, por favor intenta de nuevo.'
        });
    }
});

const GetTeacherData = async (teacherId) => {
    try {
        const response = await $.ajax({
            url: "php/teachers/routes.php",
            type: "GET",
            data: {teacherId: teacherId, action: "getTeacherData"},
        });
        if(response.success){
            FillTable(response);
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al obtener los datos',
                text: response.message,
                confirmButtonText: 'Iniciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'index.html';
                }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos del profesor',
            text: 'Ocurrió un error al obtener los datos del profesor, por favor intenta de nuevo más tarde.'
        });
    }
}

const DeleteTeacher = async (teacherId) => {
    try {
        const response = await $.ajax({
            url: "php/teachers/routes.php",
            type: "POST",
            data: {teacherId: teacherId, action: "deleteTeacher"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Profesor eliminado',
                text: response.message,
            });
            $("#teachersTable").DataTable().ajax.reload();
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message,
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el profesor',
            text: 'Ocurrió un error al eliminar el profesor, por favor intenta de nuevo más tarde.'
        });
    }
}

const UpdateTeacherData = async (teacherEditData) => {
    try {
        const response = await $.ajax({
            url: "php/teachers/routes.php",
            type: "POST",
            data: {teacherEditData: teacherEditData, action: "updateTeacherData"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Profesor actualizado',
                text: response.message,
            });
            $("#teachersTable").DataTable().ajax.reload();
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message,
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar el usuario',
            text: 'Ocurrió un error al actualizar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

//miselaneos

$("#TeacherEditModal").on("hidden.bs.modal", function() {
    ClearInputsEditTeacher();
});