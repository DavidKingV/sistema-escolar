import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingAlert } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

const callback = '../public/api.php';

$(function () {
    initializeDataTable('#schedulesTable', callback, { action: 'getSchedulesGroup' }, [
        { "data": "title", "className": "text-center" },
        { "data": "date", "className": "text-center" },
        { "data": "start", "className": "text-center" },     
        { "data": "end", "className": "text-center" },   
        { "data": "description", "className": "text-center" },      
        {"data": null, "render": function(data, type, row) {
            return `<button data-id="`+row.studentId+`" class="btn btn-info btn-circle addHours" data-bs-toggle="modal" data-bs-target="#addHoursModal"><i class="bi bi-plus-square"></i></button> <button data-id="`+row.studentId+`" data-total="`+row.total_hours+`" class="btn btn-primary btn-circle seeTotal" data-bs-toggle="modal" data-bs-target="#seeTotalModal"><i class="bi bi-eye-fill"></i></button> 
            <button id="generateReport" data-id="`+row.studentId+`" class="btn btn-success btn-circle"><i class="bi bi-file-earmark-check-fill"></i></button>`; 
            },"className": "text-center"
        }
    ]);
});

$("#formAddSchedule").on('submit', function (e) {
    e.preventDefault();
    let data = $(this).serialize();
    addSchedule(data);
});

const addSchedule = async (data) => {  
    loadingAlert(); 
    try {
        sendFetch(callback, 'POST', { action: 'addSchedule', scheduleData: data})
            .then(response => {
                if (!response.ok) {
                    throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                }
                return response.json(); // Asegúrate de que se está retornando la promesa con la conversión a JSON
            })
            .then(data => {
                if (data.success) {
                    successAlertAuto(data.message);
                    $('#formAddSchedule')[0].reset();
                } else {
                    errorAlert(data.message);
                }
            });
    } catch (error) {
        errorAlert(error);
    }
};