import { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade, initializeSubjectChangeListener, HideTab, RenderAlertMessage } from './forms.js';
import { initializeStudentDataTable, initializeStudentsUsersTable, initializeStudentsMicrosoftUsersTable, InitializeStudentGrades } from '../datatables/index.js';
import { enviarPeticionAjax } from '../utils/ajax.js';
import { sendFetch } from '../global/fetchCall.js';
import { errorAlert, successAlert, infoAlert, loadingSpinner, confirmAlert } from '../utils/alerts.js';
import { validateForm, capitalizeAllWords, capitalizeAll } from '../global/validate/index.js';

initializeStudentDataTable();
initializeStudentsUsersTable();
initializeStudentsMicrosoftUsersTable();

$(function () {
    $("#studentName, #studentNation").on("input", function() {
        this.value = capitalizeAllWords(this.value);
    });

    $("#studentCurp").on("input", function() {
        this.value = capitalizeAll(this.value);
    });

    validateForm('#addStudents', {
        studentName: {
            required: true,
            minlength: 3,
        },
        controlNumber: {
            required: true,
            minlength: 3,
        },
        controlSepNumber: {
            required: {
                depends: function() {
                    // El campo es requerido solo si no está activado el switch
                    return !$("#noExtraData").is(":checked");
                }
            },
        },
        studentGender: {
            required: true,
            valueNotEquals: "0"
        },
        studentBirthday: {
            required: true,
            date: true
        },
        studentState: {
            required: {
                depends: function() {
                    // El campo es requerido solo si no está activado el switch
                    return !$("#noExtraData").is(":checked");
                }
            },
            valueNotEquals: "0"
        },
        studentNation: {
            required: {
                depends: function() {
                    // El campo es requerido solo si no está activado el switch
                    return !$("#noExtraData").is(":checked");
                }
            },
        },
        studentCurp: {
            required: true,
            minlength: 18,
            noSpace: true
        },
        countryCode: {
            required: true,
            valueNotEquals: "0"
        },
        studentPhone: {
            required: true,
            minlength: 10,
            maxlength: 10
        },
        studentEmail: {
            required: true,
            email: true
        }
    },
    {
        studentName: {
            required: "Por favor, ingresa el nombre del estudiante.",
            minlength: "El nombre del estudiante debe tener al menos 3 caracteres."
        },
        controlNumber: {
            required: "Por favor, ingresa el número de control.",
            minlength: "El número de control debe tener al menos 3 caracteres."
        },
        controlSepNumber: {
            required: "Por favor, ingresa el número de control SEP."
        },
        studentGender: {
            required: "Por favor, selecciona el género del estudiante.",
            valueNotEquals: "Por favor, selecciona el género del estudiante."
        },
        studentBirthday: {
            required: "Por favor, ingresa la fecha de nacimiento del estudiante.",
            date: "Por favor, ingresa una fecha válida."
        },
        studentState: {
            required: "Por favor, selecciona el estado civil del estudiante.",
            valueNotEquals: "Por favor, selecciona el estado civil del estudiante."
        },
        studentNation: {
            required: "Por favor, ingresa la nacionalidad del estudiante."
        },
        studentCurp: {
            required: "Por favor, ingresa la CURP del estudiante.",
            minlength: "La CURP debe tener al menos 18 caracteres."
        },
        countryCode: {
            required: "Por favor, selecciona el código de país.",
            valueNotEquals: "Por favor, selecciona el código de país."
        },
        studentPhone: {
            required: "Por favor, ingresa el número de teléfono del estudiante.",
            minlength: "El número de teléfono debe tener al menos 10 caracteres.",
            maxlength: "El número de teléfono no debe exceder los 10 caracteres."
        },
        studentEmail: {
            required: "Por favor, ingresa el correo electrónico del estudiante.",
            email: "Por favor, ingresa un correo electrónico válido."
        }
    });

    let isLoading = false;
    
    $('#microsoftUser').select2({
        theme: "bootstrap-5",
        minimumInputLength: 2, // empieza a buscar con 2 caracteres
        ajax: {
            url: '../../backend/students/routes.php',
            method: 'GET',
            dataType: 'json',
            delay: 250,
            data: function (params) {
                isLoading = true; // empieza carga
                return {
                    studentName: params.term, // término que el usuario escribe
                    action: 'searchMicrosoftUser'
                };
            },
            processResults: function (data) {
                isLoading = false; // termina carga
                // tu backend devuelve un solo usuario, lo empaquetamos como array
                if (data.success && data.data && data.data.success) {
                    const user = data.data;
                    return {
                        results: [{
                            id: user.id,
                            text: user.displayName,
                            mail: user.mail,
                        }]
                    };
                } else {
                    return { results: [] };
                }
            },
            cache: true
        },
        minimumInputLength: 2,
            language: {
                inputTooShort: function() {
                    return "Por favor ingrese al menos 2 caracteres";
                },
                searching: function() {
                    return "Buscando...";
                },
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
    });

    $('#microsoftUser').on('select2:closing', function (e) {
        if (isLoading) {
            e.preventDefault(); // no dejar cerrar
        }
    });

    /// Cuando el usuario selecciona un resultado
    $('#microsoftUser').on('select2:select', function (e) {
        const data = e.params.data;
        const usuario = obtenerUsuario(data.mail); // tu función existente

        // ✅ Asegurar que la opción seleccionada se guarda en el select
        if ($('#microsoftUser').find("option[value='" + data.id + "']").length === 0) {
            const newOption = new Option(data.text, data.id, true, true);
            $('#microsoftUser').append(newOption).trigger('change');
        }
        // ✅ Rellenar tus otros campos
        $("#microsoftId").val(data.id).prop("disabled", false);
        $("#microsoftEmail").val(data.mail).prop("disabled", false);
        $("#controlNumber").val(usuario);
        $("#studentName").val(data.text);
        $("#microsoftDiv").show();
    });
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

$('#studentTable').on('click', '.deleteStudent', function() {

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

$('#studentTable').on('click', '.badge', async function() {
    let studentId = $(this).data('id');
    let studentName = $(this).data('name');
    let studentStatus = $(this).data('status');

    await $.post('../modals/studentStatus.modal.php', { studentId: studentId,studentName: studentName, studentStatus: studentStatus }, function (data) {
        $("#statusModal").modal('show');
        $('#statusModalBody').html(data);
    });

    if(!studentId){
        errorAlert('ID del estudiante no proporcionado');
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
                confirmAlert('Seguro que deseas dejar estos campos vacios', 'Si', 'No', function() {
                    UpdateStudent(studentData);
                    $('#StutentEditModal').modal('hide');                    
                });                                
            }
        }
    });
});

$("#addStudents").on( "submit", function( event ) {
    event.preventDefault();
    const studentData = $(this).serialize();

    if($(this).valid()){
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
    }else{
        infoAlert('Por favor, verifica que todos los campos estén llenos y sean correctos.');
    }
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

$("#addGradeStudent").on("submit", function(event){
    event.preventDefault();
    let studentGradeData = $(this).serialize();
    const studentId = localStorage.getItem('studentIdJTW');

    if(studentId){
        studentGradeData += `&studentId=${encodeURIComponent(studentId)}`;
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

$("#studentGroupDetailsForm").on("submit", function(event){
    event.preventDefault();
    let studentGroupData = $(this).serialize();

    let urlParams = new URLSearchParams(window.location.search);
    studentGroupData += `&studentId=${encodeURIComponent(urlParams.get('id'))}`;

    Swal.fire({
        title: 'Se agregara al estudiante al grupo seleccionado',
        text: '¿Estas seguro de agregar al estudiante al grupo seleccionado?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddStudentGroup(studentGroupData);
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



const AddGradeStudent = async (studentGradeData) => {
    try {
        const response = await $.ajax({
            url: '../../backend/students/routes.php',
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

export const GetChildSubjectsNames = async (idSubject) => {

    const GetChildSubjectSelect = async () => {
        try {
            const response = await $.ajax({
                url: '../../backend/students/routes.php',
                type: 'GET',
                data: {idSubject: idSubject, action: 'getChildSubjectsNames'}
            });
            return response;
        } catch (error) {
            throw new Error('Error al obtener los datos');
        }
    };

    try {
        const childSubjects = await GetChildSubjectSelect();

        if (!childSubjects || childSubjects.length === 0) {
            console.log('No se encontraron materias para la carrera seleccionada');
            return;
        }

        let $select = $('.subjectChildName');
        $.each(childSubjects, function(index, childSubject) {
            if (childSubject.success !== false) {
                let $option = $('<option>', {
                    value: childSubject.id_child_subject,
                    text: childSubject.name_child_subject
                });

                $select.append($option);
            }
        });

        $select.select2({
            theme: "bootstrap-5",
            disabled: false,
            placeholder: 'Selecciona la submateria',
        });

    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    }

};


const UpdateStudentUser = async (studentUserData) => {
    try {
        const response = await $.ajax({
            url: '../../backend/students/routes.php',
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
            url: '../../backend/students/routes.php',
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
            url: '../../backend/students/routes.php',
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
            url: '../../backend/students/routes.php',
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
            url: '../../backend/students/routes.php',
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
                    window.location.href = '../index.php';
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
            url: '../backend/students/routes.php',
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
                    window.location.href = 'index.php';
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
            url: '../../backend/students/routes.php',
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
            $("#microsoftId, #microsoftEmail").val('');
            $("#microsoftId, #microsoftEmail").prop("disabled", true);
            $("#microsoftDiv").hide();
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
                    window.location.href = '../index.php';
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
            url: '../backend/students/routes.php',
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
            url: '../backend/students/routes.php',
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

const GetGroupsNames = async () => {

    const GetGroupSelect = async () => {
        try {
            const response = await $.ajax({
                url: '../../backend/students/routes.php',
                type: 'GET',
                data: {action: 'getGroupsNames'}
            });
            return response;
        } catch (error) {
            throw new Error('Error al obtener los datos');
        }
    };

    try {
        const groups = await GetGroupSelect();

        if (!groups || groups.length === 0) {
            console.log('No se encontraron materias para la carrera seleccionada');
            return;
        }

        let $select = $('#studentIdGroup');
        $.each(groups, function(index, groupsNames) {
            if (groupsNames.success !== false) {
                let $option = $('<option>', {
                    value: groupsNames.id,
                    text: groupsNames.name
                });

                $select.append($option);
            }
        });

        $select.select2({
            theme: "bootstrap-5",
            disabled: false,
            placeholder: 'Selecciona un grupo',
        });

    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    }

};

const AddStudentGroup = async (studentGroupData) => {
    try {
        const response = await $.ajax({
            url: '../../backend/students/routes.php',
            type: 'POST',
            data: {studentGroupData: studentGroupData, action: 'addStudentGroup'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Estudiante agregado al grupo',
                text: response.message,
                timer: 3000
            });
            // Reload the table
            window,location.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar el estudiante al grupo',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el estudiante al grupo',
            text: 'Ocurrió un error al agregar el estudiante al grupo, por favor intenta de nuevo más tarde.'
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


/**
 * Extrae el usuario (local-part) de un email y lo convierte a mayúsculas.
 * @param {string} email - Dirección de correo, p. ej. "2409LF01001@escuela.edu.mx"
 * @returns {string|null} El usuario en mayúsculas, o null si el email no es válido.
 */
function obtenerUsuario(email) {
  if (typeof email !== 'string') return null;

  // 1) Limpia espacios
  const limpio = email.trim();
  if (!limpio) return null;

  // 2) Busca el separador '@'
  const atIndex = limpio.indexOf('@');
  if (atIndex <= 0) return null; // sin usuario o sin '@'

  // 3) Toma solo la parte antes de '@'
  let usuario = limpio.slice(0, atIndex);

  // 4) Normaliza y convierte letras a mayúsculas (respetando acentos/ñ si aparecieran)
  usuario = usuario.normalize('NFC').toLocaleUpperCase('es-MX');

  return usuario;
}
