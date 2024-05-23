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

export { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser};