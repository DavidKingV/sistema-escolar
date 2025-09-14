import { initializeTeachersDataTable, initializeTeachersUsersTable } from './../datatables/index.js';
import { FillTable, ClearInputsEditTeacher, ClearInputsUserTeacher } from '../teachers/forms.js';

initializeTeachersDataTable();
initializeTeachersUsersTable();

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

$("#teacherUsersTable").on("click", ".addUserTeachers", function() {
    let teacherId = $(this).data("id");
    let teacherName = $(this).data("name");
    $("#submitUser").attr('disabled', true);
    if(teacherId){
        $("#teacherUserId").val(teacherId);
        $("#teacherUserName").val(teacherName);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del profesor, por favor intenta de nuevo.'
        });
    
    }
});

$("#teacherUserAdd").on("blur", function(){
    $(".userSuccess").text('');
    let teacherUserAdd = $(this).val();
    if(teacherUserAdd){
        setTimeout(() => {
            VerifyTeacherUser(teacherUserAdd);
        }, 1000);
    }else{
        $("#teacherUserAdd-error").text('Por favor, ingresa un usuario.');
    }
});

$("#teacherUserAddEdit").on("blur", function(){
    $(".userSuccess").text('');
    let teacherUserAdd = $(this).val();
    if(teacherUserAdd){
        setTimeout(() => {
            VerifyTeacherUser(teacherUserAdd);
        }, 1000);
    }else{
        $("#teacherUserAdd-error").text('Por favor, ingresa un usuario.');
    }
});

$("#addTeachersUsers").submit(function(e) {
    e.preventDefault();
    const teacherUserData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar el usuario al profesor?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddTeacherUser(teacherUserData);
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

$("#teacherUsersTable").on("click", ".desactivateTeacherUser", function() {
    let teacherUserId = $(this).data("id");
    if(teacherUserId){
        Swal.fire({
            title: '¿Estás seguro de desactivar el usuario del profesor?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                DesactivateTeacherUser(teacherUserId);
            }
        });
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del usuario, por favor intenta de nuevo.'
        });
    }
});

$("#teacherUsersTable").on("click", ".reactivateTeacherUser", function() {
    let teacherUserId = $(this).data("id");
    if(teacherUserId){
        Swal.fire({
            title: '¿Estás seguro de reactivar el usuario del profesor?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                ReactivateTeacherUser(teacherUserId);
            }
        });
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del usuario, por favor intenta de nuevo.'
        });
    }
});

$("#teacherUsersTable").on("click", ".editTeacherUser", function() {
    let teacherUserId = $(this).data("id");
    let teacherUserName = $(this).data("name");
    let teacherUserUser = $(this).data("user");
    if(teacherUserId){
        $("#teacherUserIdEdit").val(teacherUserId);
        $("#teacherUserNameEdit").val(teacherUserName);
        $("#teacherUserAddEdit").val(teacherUserUser);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID del usuario, por favor intenta de nuevo.'
        });
    }
});

const AddTeacherUser = async (teacherUserData) => {
    try {
        const response = await $.ajax({
            url: "../../backend/teachers/routes.php",
            type: "POST",
            data: {teacherUserData: teacherUserData, action: "addTeacherUser"},
        });
        if(response.success){
            console.log(response);
            Swal.fire({
                icon: 'success',
                title: 'Usuario agregado',
                text: response.message,
            });
            $("#teacherUsersTable").DataTable().ajax.reload();
            $("#addTeachersUsers")[0].reset();
            $("#teacherUserModal").modal('hide');
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message,
            });
        }
    } catch (error) {
        console.error("Error en la solicitud AJAX:", error);
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el usuario',
            text: 'Ocurrió un error al agregar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

const VerifyTeacherUser = async (teacherUserAdd) => {
    try {
        const response = await $.ajax({
            url: "../../backend/teachers/routes.php",
            type: "POST",
            data: {teacherUserAdd: teacherUserAdd, action: "verifyTeacherUser"},
        });
        if(response.success){
            if(!response.user){
                $(".userError").text('El usuario ya está asignado a un profesor.');
            }else{
                $("#submitUser").attr('disabled', false);
                $(".userError").text('');
                $(".userSuccess").text('Usuario disponible.');
            }
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al verificar el usuario',
                text: response.message,
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al verificar el usuario',
            text: 'Ocurrió un error al verificar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

const DesactivateTeacherUser = async (teacherUserId) => {
    try {
        const response = await $.ajax({
            url: "../../backend/teachers/routes.php",
            type: "POST",
            data: {teacherUserId: teacherUserId, action: "desactivateTeacherUser"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Usuario desactivado',
                text: response.message,
            });
            $("#teacherUsersTable").DataTable().ajax.reload();
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
            title: 'Error al desactivar el usuario',
            text: 'Ocurrió un error al desactivar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

const ReactivateTeacherUser = async (teacherUserId) => {
    try {
        const response = await $.ajax({
            url: "../../backend/teachers/routes.php",
            type: "POST",
            data: {teacherUserId: teacherUserId, action: "reactivateTeacherUser"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Usuario reactivado',
                text: response.message,
            });
            $("#teacherUsersTable").DataTable().ajax.reload();
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
            title: 'Error al reactivar el usuario',
            text: 'Ocurrió un error al reactivar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

$("#addTeachers").submit(function(e) {
    e.preventDefault();
    const teacherData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar al profesor?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                addTeachers(teacherData);
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

$("#editTeachersUsers").submit(function(e) {
    e.preventDefault();
    const teacherUserEditData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de actualizar los datos del usuario del profesor?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateTeacherUserData(teacherUserEditData);
                $('#TeacherUserEditModal').modal('hide');
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

const UpdateTeacherUserData = async (teacherUserEditData) => {
    try {
        const response = await $.ajax({
            url: "../backend/teachers/routes.php",
            type: "POST",
            data: {teacherUserEditData: teacherUserEditData, action: "UpdateTeacherUserData"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Usuario actualizado',
                text: response.message,
            });
            $("#teacherUsersTable").DataTable().ajax.reload();
            $("#TeacherUserEditModal").modal("hide");
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

const GetTeacherData = async (teacherId) => {
    try {
        const response = await $.ajax({
            url: "../backend/teachers/routes.php",
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

const addTeachers = async (teacherData) => {
    try {
        const response = await $.ajax({
            url: "../../backend/teachers/routes.php",
            type: "POST",
            data: {teacherData: teacherData, action: "addTeacher"},
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Profesor agregado',
                text: response.message,
            });
            $("#teachersTable").DataTable().ajax.reload();
            $("#addTeachers")[0].reset();
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.message,
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el profesor',
            text: 'Ocurrió un error al agregar el profesor, por favor intenta de nuevo más tarde.'
        });
    }
}

const DeleteTeacher = async (teacherId) => {
    try {
        const response = await $.ajax({
            url: "../backend/teachers/routes.php",
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
            url: "../backend/teachers/routes.php",
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
$("#showPasswordToggle").on("click", function(){
    let password = $("#teacherUserPass");
    if(password.attr('type') == 'password'){
        password.attr('type', 'text');
    }else{
        password.attr('type', 'password');
    }
});

$("#editUserNameteacher").on("click", function(){
    let inputuser = $("#teacherUserAddEdit");
    if (inputuser.attr('readonly')) {
        inputuser.attr('readonly', false);
    }else{
        inputuser.attr('readonly', true);
    }
});

$("#showPasswordToggleEdit").on("click", function(){
    let password = $("#teacherUserPassEdit");
    if(password.attr('type') == 'password'){
        password.attr('type', 'text');
    }else{
        password.attr('type', 'password');
    }
});

$("#TeacherEditModal").on("hidden.bs.modal", function() {
    ClearInputsEditTeacher();
});

$("#teacherUserModal").on("hidden.bs.modal", function() {
    ClearInputsUserTeacher();
});