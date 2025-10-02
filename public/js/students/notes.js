import { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade, initializeSubjectChangeListener, HideTab, RenderAlertMessage } from './forms.js';
import { initializeStudentDataTable, initializeStudentsUsersTable, initializeStudentsMicrosoftUsersTable, InitializeStudentGrades } from '../datatables/index.js';
import { enviarPeticionAjax } from '../utils/ajax.js';
import { errorAlert, successAlert, infoAlert, loadingSpinner } from '../utils/alerts.js';

$(function () {
    let urlParams = new URLSearchParams(window.location.search);
    let studentIdGroup = urlParams.get('student'); 
    let token = urlParams.get('encode');
    if (token && (token.startsWith("'") || token.startsWith('"'))) {
        token = token.slice(1, -1);
    }
   

    if (studentIdGroup) {
        VerifyToken(studentIdGroup, token)
        .then((response) => {
            if(response){
                VerifyGroupStudent(studentIdGroup).then((response) => {
                    if(response){
                        InitializeStudentGrades(studentIdGroup);
                        HideTab("#alertDisplay");
                        RenderAlertMessage("El alumno ya tiene un grupo asignado", "info", "#studentGroupDetails");
                        
                        localStorage.setItem('studentIdJTW', studentIdGroup);
                        var studentIdJTW = localStorage.getItem('studentIdJTW');
                        
                        if(!studentIdJTW){
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

    initializeSubjectChangeListener(".subjectName");
});

const VerifyGroupStudent = async (studentIdGroup) => {
    try {
        const response = await $.ajax({
            url: '../../backend/students/routes.php',
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
                GetGroupsNames();
                return false;
            }else{
                GetSubjectsNames(response.id_carrer);
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
            text: 'Ocurrió un error al verificar el grupo, por favor intenta de nuevo más tarde. + ' + error
        });
    }
}

const VerifyToken = async (studentId, token) => {
    try {
        const response = await $.ajax({
            url: '../../backend/students/routes.php',
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

const GetSubjectsNames = async (carrerId) => {

    const GetSubjectSelect = async () => {
        try {
            const response = await $.ajax({
                url: '../../backend/students/routes.php',
                type: 'GET',
                data: {carrerId: carrerId, action: 'getSubjectsNames'}
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
                    text: subject.name_subject,
                    class: subject.id_child_subject
                });

                $select.append($option);
                
            }
        });

        $select.select2({
            theme: "bootstrap-5",
            placeholder: 'Selecciona la materia',
        });

    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } 
   
};