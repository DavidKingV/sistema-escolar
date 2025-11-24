import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

const callback = '../api.php';

$(function() {
    
    initializeDataTable('#studentsHours', callback, { action: 'getStudentsHours' }, [
        { "data": "nombre", "className": "text-center" },
        { "data": "total_hours", "className": "text-center" },
        { "data": "date", "className": "text-center" },        
        {"data": null, "render": function(data, type, row) {
            return `<button data-id="`+row.studentId+`" class="btn btn-info btn-circle addHours" data-bs-toggle="modal" data-bs-target="#addHoursModal"><i class="bi bi-plus-square"></i></button> 
            <button data-id="`+row.studentId+`" data-total="`+row.total_hours+`" class="btn btn-primary btn-circle seeTotal" data-bs-toggle="modal" data-bs-target="#seeTotalModal"><i class="bi bi-eye-fill"></i></button> 
            <button id="generateReport" data-id="`+row.studentId+`" class="btn btn-success btn-circle"><i class="bi bi-file-earmark-check-fill"></i></button>`; 
            },"className": "text-center"
        }
    ]);    

    $('#studentsHours').on('click', '.seeTotal', function() {
        const id = $(this).data('id');
        const total = $(this).data('total');
        modalTotal(id, total);
    });

    $('#studentsHours').on('click', '.addHours', function() {
        const id = $(this).data('id');
        addHours(id);
    });

});

const modalTotal = async (id, total) => {
    loadingSpinner('#seeTotalModalBody');
    await $.post('../modals/seeTotal.Modal.php', { id: id, total: total}, function (data) {
        $('#seeTotalModalBody').html(data);
    });
}

const addHours = async (id) => {
    await $.post('../modals/addHours.Modal.php', { id: id }, function (data) {
        $('#addHoursModalBody').html(data);
    });
}