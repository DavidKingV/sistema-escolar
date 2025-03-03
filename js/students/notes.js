import { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade, initializeSubjectChangeListener, HideTab, RenderAlertMessage } from './forms.js';
import { initializeStudentDataTable, initializeStudentsUsersTable, initializeStudentsMicrosoftUsersTable, InitializeStudentGrades } from '../datatables/index.js';
import { enviarPeticionAjax } from '../utils/ajax.js';
import { errorAlert, successAlert, infoAlert, loadingSpinner } from '../utils/alerts.js';

$(function () {
    let urlParams = new URLSearchParams(window.location.search);
    console.log(currentPath);
    let studentIdGroup = urlParams.get('student'); 
    let token = urlParams.get('jtw');       

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