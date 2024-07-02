import { GetChildSubjectsNames } from "./index.js";
//llenado de los inputs para edicion
function FillTable(response){
    $("#idStudentDB").val(response.id);
    $("#controlNumber").val(response.no_control);
    $("#studentName").val(response.name);

    $("#studentGender option[value='" + response.gender + "']").prop('selected', true);

    var birthdateISO = response.birthdate.split("/").reverse().join("-");
    $("#studentBirthday").val(birthdateISO);

    $("#studentState option[value='" + response.civil_status + "']").prop('selected', true);

    $("#studentState").val(response.civil_status);
    $("#studentNation").val(response.nationality);
    $("#studentCurp").val(response.curp);
    $("#studentPhone").val(response.phone);
    $("#studentEmail").val(response.email);
}

//funcion para limpiar los inputs
function ClearInputsEditEstudents(){
    $("#idStudentDB").val("");
    $("#controlNumber").val("");
    $("#studentName").val("");
    $("#studentGender").val("");
    $("#studentBirthday").val("");
    $("#studentState").val("");
    $("#studentNation").val("");
    $("#studentCurp").val("");
    $("#studentPhone").val("");
    $("#studentEmail").val("");
}

function ClearStudensAddUser(){
    $("#addStudentsUsers")[0].reset();
    $("#addStudentsUsers").validate().resetForm();
    (".userError", ".userSuccess").html("");
}

function ClearStudensEditUser(){
    $("#editStudentsUsers")[0].reset();
    $("#editStudentsUsers").validate().resetForm();
    $(".userError", ".userSuccess").html("");
}


function AverageGrade(){
    let continuos_grade = parseFloat($("#gradeCont").val());
    let exam_grade = parseFloat($("#gradetest").val());

    let average = (continuos_grade + exam_grade) / 2;

    $("#gradefinal").val(average);
}

function initializeSubjectChangeListener(selector) {
    $(selector).on('change', function() {
        if ($(this).attr('class')) {
            let idChildSubject = $(this).find(':selected').attr('class');
            if (typeof idChildSubject !== "undefined") {
                let idSubject = $(this).val();
                GetChildSubjectsNames(idSubject);
            } else {
                let select2Element = $(".subjectChildName");
                select2Element.val(null).trigger('change');  // Limpiar el valor seleccionado
                select2Element.empty();  // Borrar las opciones
                select2Element.select2({
                    theme: "bootstrap-5", // Puedes personalizar el placeholder
                    placeholder: 'Sin submaterias',
                    disabled: true,
                    allowClear: true
                });
            }
        } else {
            return;
        }
    });
}

export { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade, initializeSubjectChangeListener};