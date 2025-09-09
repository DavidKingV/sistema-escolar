import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

let urlParams = new URLSearchParams(window.location.search);
let studentIdGroup = urlParams.get('student'); 

let api = '../api.php';

let studentName;

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
                        studentName = data.studentName;
                        $("#placeholder").text('Calificaciones de '+data.studentName).attr("class", "studentName");
                    } else {
                        errorAlert(data.message);
                    }
                });     
    }catch(error){
        errorAlert(error);
    }
}

$("#studentGradesTable").on('click', '.studentGrade', function () {
    let subjectId = $(this).data('subject');
    let subjectName = $(this).data('subjectname');

    let subjectChildId = $(this).data('subjectchild');
    let subjectChildName = $(this).data('subjectchildname');

    let gradeId = $(this).data('grade');

    $("#makeOverExamModalLabel").html(
        'Recursamiento para ' + studentName + ' - ' + subjectName + '  ' + (subjectChildName ? subjectChildName : '')
      );
    $("#makeOverExamModal").modal('show');
    $.post('/modals/makeOverExam.Modal.php', { studentId: studentIdGroup, subjectId: subjectId, subjectName : subjectName, subjectChildName: subjectChildName, subjectChildId: subjectChildId, gradeId: gradeId }, function (data) {
        $('#makeOverExamModalBody').html(data);
    });
});

$("#studentGradesTable").on('click', '.mekeOver', function () {
    let makeOverId;

    if($(this).data('makeoverid')){
        makeOverId = $(this).data('makeoverid');
    }else{
        makeOverId = null;
    }

    $("#makeOverViewModalLabel").html(
        'calificacion de Recursamiento para ' + studentName
      );
    $("#makeOverViewModal").modal('show');
    $.post('/modals/viewMakeOver.Modal.php', { studentId: studentIdGroup, makeOverId: makeOverId }, function (data) {
        $('#makeOverViewModalBody').html(data);
    });
});

$("#studentGradesTable").on('click', '.makeOverChild', function () {  
    let makeOverChildId;

    if($(this).data('makeoverchildid')){
        makeOverChildId = $(this).data('makeoverchildid');
    }else{
        makeOverChildId = null;
    }

    $("#makeOverViewModalLabel").html(
        'calificacion de Recursamiento para ' + studentName
      );
    $("#makeOverViewModal").modal('show');
    $.post('/modals/viewMakeOver.Modal.php', { studentId: studentIdGroup, makeOverChildId: makeOverChildId }, function (data) {
        $('#makeOverViewModalBody').html(data);
    });
});