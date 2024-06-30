import { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade } from './forms.js';
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
    $("#submitUser").attr('disabled', true);
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

$("#studentsUsersTable").on( "click", ".editStudentUser", function() {
    let studentId = $(this).data('id');
    let studentName = $(this).data('name');
    let studentUser = $(this).data('user');
    if(studentId && studentName){
        $("#studentUserIdEdit").val(studentId);
        $("#studentUserNameEdit").val(studentName);
        $("#studentUserAddEdit").val(studentUser);
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para asignar un usuario.'
        });
    }
});

$("#studentUserAdd").on("blur", function(){
    $(".userSuccess").hide();
    let studentUserAdd = $(this).val();
    if(studentUserAdd){
        setTimeout(() => {
            VerifyUser(studentUserAdd);
        }, 1000);
    }else{
        $("#studentUserAdd-error").text('Por favor, ingresa un usuario.');
    }
});

$("#studentUserAddEdit").on("blur", function(){
    $(".userSuccess").hide();
    let studentUserAdd = $(this).val();
    if(studentUserAdd){
        setTimeout(() => {
            VerifyUser(studentUserAdd);
        }, 1000);
    }else{
        $("#studentUserAddEdit-error").text('Por favor, ingresa un usuario.');
    }
});

$("#addStudentsUsers").on( "submit", function( event ) {
    event.preventDefault();
    const studentUserData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar el usuario al estudiante?',
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
                AddStudentUser(studentUserData);
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

$("#studentsUsersTable").on("click", ".reactivateStudentUser", function(){
    let studentId = $(this).data('id');
    if(studentId){
        Swal.fire({
            title: '¿Estás seguro de reactivar el usuario?',            
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, reactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                ReactivateStudentUser(studentId);
            }
        });
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para reactivar el usuario.'
        });
    }
});

$("#studentsUsersTable").on("click", ".desactivateStudentUser", function(){
    let studentId = $(this).data('id');
    if(studentId){
        Swal.fire({
            title: '¿Estás seguro de desactivar el usuario?',            
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, desactivar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                DesactivateStudentUser(studentId);
            }
        });
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para desactivar el usuario.'
        });
    }
});

$("#editStudentsUsers").on( "submit", function( event ) {
    event.preventDefault();
    const studentUserData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de actualizar el usuario del estudiante?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateStudentUser(studentUserData);
            $('#StutentUserEditModal').modal('hide');
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

$("#studentTable").on("click", ".GradeStudent", function(){
    let studentId = $(this).data('id');
    let token = $(this).data('token');
    if(studentId){
        window.location.href = `alumnos/calificaciones.php?id=${studentId}&jtw=${token}`;
    }else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para asignar un usuario.'
        });
    }
});

$("#addGradeStudent").on("submit", function(event){
    event.preventDefault();
    const studentGradeData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar la calificación al estudiante?',
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
                AddGradeStudent(studentGradeData);
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

$("#gradeCont, #gradetest").on("input", function(){
    AverageGrade();
});

$(function () {
    let currentPath = window.location.pathname;
    let specificPath = "/alumnos/calificaciones.php";

    if (currentPath === specificPath) {
        let urlParams = new URLSearchParams(window.location.search);
        let studentIdGroup = urlParams.get('id'); 
        let token = urlParams.get('jtw');       

        if (studentIdGroup) {
            VerifyToken(studentIdGroup, token)
            .then((response) => {
                if(response){
                    VerifyGroupStudent(studentIdGroup).then((response) => {
                        if(response){
                            
                            localStorage.setItem('studentIdJTW', studentIdGroup);
                            var studentIdJTW = localStorage.getItem('studentIdJTW');
                            
                            if(studentIdJTW){
                                $("#studentIdDB").val(studentIdJTW);
                            }else{
                                Swal.fire({
                                    icon: 'error',
                                    title: 'ID del estudiante no proporcionado',
                                    text: 'Por favor proporciona un ID válido para asignar un usuario.'
                                }).then((result) => {
                                    if(result.isConfirmed){
                                        window.location.href = '../alumnos.php';
                                    }
                                });
                            }

                            $(window).on('beforeunload', function() {
                                console.log("Limpiando el localStorage");
                                localStorage.removeItem('studentIdJTW');
                            });

                        }
                    });
                }
            });
        
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'No se ha podido obtener el ID del alumno, por favor intenta de nuevo.',
            });
        }


    }
});

const VerifyToken = async (studentId, token) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'GET',
            data: {studentId: studentId, token: token, action: 'verifyToken'}
        });
        if(response.success){
            if(!response.token){
                Swal.fire({
                    icon: 'error',
                    title: 'Token no válido',
                    text: 'El token proporcionado no es válido, por favor intenta de nuevo.'
                });
            }else{
                return true;
            }
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al verificar el token',
                text: response.message
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = '../alumnos.php';
                }
            });
            return false;
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al verificar el token',
            text: 'Ocurrió un error al verificar el token, por favor intenta de nuevo más tarde.'
        });
    }
}

const AddGradeStudent = async (studentGradeData) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentGradeData: studentGradeData, action: 'addGradeStudent'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Calificación agregada',
                text: response.message,
                timer: 1000
            });
            // Reload the table
            //$('#addGradesTable').DataTable().ajax.reload();
            //$("#gradesStudentTable").DataTable().ajax.reload();
            $('#addGradeStudent')[0].reset();
            $("#subject").trigger('change'); 
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar la calificación',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar la calificación',
            text: 'Ocurrió un error al agregar la calificación, por favor intenta de nuevo más tarde.' + error
        });
    }
}

const VerifyGroupStudent = async (studentIdGroup) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'GET',
            data: {studentIdGroup: studentIdGroup, action: 'verifyGroupStudent'}
        });
        if(response.success){
            if(!response.group){
                Swal.fire({
                    icon: 'error',
                    title: 'Grupo no encontrado',
                    text: 'No se encontró el grupo al que pertenece el estudiante, por favor verifica que el ID sea correcto.'
                });
                return false;
            }else{
                GetSubjectsNames(response.id_career);
                return true;
            }
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al verificar el grupo',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al verificar el grupo',
            text: 'Ocurrió un error al verificar el grupo, por favor intenta de nuevo más tarde.'
        });
    }
}

const GetSubjectsNames = async (careerId) => {

    const GetSubjectSelect = async () => {
        try {
            const response = await $.ajax({
                url: '../php/students/routes.php',
                type: 'GET',
                data: {careerId: careerId, action: 'getSubjectsNames'}
            });
            return response;
        } catch (error) {
            console.error('Error al obtener los datos:', error);
            throw new Error('Error al obtener los datos');
        }
    };

    try {
        const subjects = await GetSubjectSelect();

        if (!subjects || subjects.length === 0) {
            console.log('No se encontraron materias para la carrera seleccionada');
            return;
        }

        let $select = $('.subjectName');
        $.each(subjects, function(index, subject) {
            if (subject.success !== false) {
                let $option = $('<option>', {
                    value: subject.id_subject,
                    text: subject.name_subject
                });

                $select.append($option);
                
                if(subject.id_child_subject && subject.name_child_subject){
                    let $optionChild = $('<option>', {
                        value: 'sub'+subject.id_child_subject,
                        text: subject.name_child_subject
                    });
                    $select.append($optionChild);
                }
            }
        });

        $select.select2({
            theme: "bootstrap-5",
            placeholder: 'Selecciona la materia',
        });

        $select.on('change', function () {
            let selectedValue = $(this).val(); // Obtener el valor seleccionado
            //si selectedValue empieza con sub 
            if(selectedValue.startsWith('sub')){
                selectedValue = selectedValue.substring(3);
                $("#subjectChild").val(selectedValue);
            }else{
                $("#subjectChild").val('');
            }

        });

    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } 
   
};


const UpdateStudentUser = async (studentUserData) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentUserData: studentUserData, action: 'updateStudentUser'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Usuario actualizado',
                text: response.message
            });
            // Reload the table
            $('#studentsUsersTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar el usuario',
                text: response.message
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

const DesactivateStudentUser = async (studentId) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentId: studentId, action: 'desactivateStudentUser'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Usuario desactivado',
                text: response.message
            });
            // Reload the table
            $('#studentsUsersTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al desactivar el usuario',
                text: response.message
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

const ReactivateStudentUser = async (studentId) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentId: studentId, action: 'reactivateStudentUser'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Usuario reactivado',
                text: response.message
            });
            // Reload the table
            $('#studentsUsersTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al reactivar el usuario',
                text: response.message
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

const AddStudentUser = async (studentUserData) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentUserData: studentUserData, action: 'addStudentUser'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Usuario agregado',
                text: response.message
            });
            // Reload the table
            $('#studentsUsersTable').DataTable().ajax.reload();
            $('#StutentUserModal').modal('hide');
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar el usuario',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el usuario',
            text: 'Ocurrió un error al agregar el usuario, por favor intenta de nuevo más tarde.'
        });
    }
}

const VerifyUser = async (studentUserAdd) => {
    try {
        const response = await $.ajax({
            url: '../php/students/routes.php',
            type: 'POST',
            data: {studentUserAdd: studentUserAdd, action: 'verifyStudentUser'}
        });
        if(response.success){
            if(!response.user){
                $(".userSuccess").hide();
                $(".userError").text('El usuario ya está asignado a un estudiante.');
            }else{
                $("#submitUser").attr('disabled', false);
                $(".userError").hide();
                $(".userSuccess").show();
                $(".userSuccess").text('Usuario disponible.');
            }
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al verificar el usuario',
                text: response.message,
                confirmButtonText: 'Iniciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = '../index.html';
                }
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al verificar el usuario',
            text: 'Ocurrió un error al verificar el usuario, por favor intenta de nuevo más tarde.'
        });
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
                text: response.message,
                confirmButtonText: 'Iniciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = '../index.html';
                }
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

//miselanios
$("#showPasswordToggle").on("click", function(){
    let password = $("#studentUserPass");
    if(password.attr('type') == 'password'){
        password.attr('type', 'text');
    }else{
        password.attr('type', 'password');
    }
});

$("#showPasswordToggleEdit").on("click", function(){
    let password = $("#studentUserPassEdit");
    if(password.attr('type') == 'password'){
        password.attr('type', 'text');
    }else{
        password.attr('type', 'password');
    }
});

$("#editUserNameStudent").on("click", function(){
    let inputuser = $("#studentUserAddEdit");
    if (inputuser.attr('readonly')) {
        inputuser.attr('readonly', false);
    }else{
        inputuser.attr('readonly', true);
    }
});


//al cerra el modal limpiar los inputs
$('#StutentEditModal').on('hidden.bs.modal', function() {
    ClearInputsEditEstudents();
});

$('#StutentUserModal').on('hidden.bs.modal', function() {
    ClearStudensAddUser();
});

$('#StutentUserEditModal').on('hidden.bs.modal', function() {
    ClearStudensEditUser();
});