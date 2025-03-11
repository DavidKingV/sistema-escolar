import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

let urlParams = new URLSearchParams(window.location.search);
let studentIdGroup = urlParams.get('student'); 

let api = '../public/api.php';

$(function () {
    getStudentName(studentIdGroup);
});

const getStudentName = async (studentIdGroup) => {
    try{
        sendFetch(api, 'POST', { action: 'getStudentName', studentId: studentIdGroup })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                    }
                    return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                })
                .then(data => {
                    if (data.success) {
                        $("#placeholder").text('Calificaciones de '+data.studentName).attr("class", "studentName");
                    } else {
                        errorAlert(data.message);
                    }
                });     
    }catch(error){
        errorAlert(error);
    }
}