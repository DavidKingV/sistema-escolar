//llenado de los inputs para edicion
function FillTable(response){
    $("#idTeacherEdit").val(response.id);
    $("#teacherNameEdit").val(response.name);

    $("#teacherGenderEdit option[value='" + response.gender + "']").prop('selected', true);

    var birthdateISO = response.birthdate.split("/").reverse().join("-");
    $("#teacherBirthdayEdit").val(birthdateISO);

    $("#teacherStateEdit option[value='" + response.civil_status + "']").prop('selected', true);

    $("#teacherPhoneEdit").val(response.phone);
    $("#teacherEmailEdit").val(response.email);
}

function ClearInputsEditTeacher(){
    $("#editTeacherForm").trigger("reset");
    $("#editTeacherForm").validate().resetForm();

    $("#idTeacherEdit").val("");
    $("#teacherNameEdit").val("");
    $("#teacherGenderEdit").val("");
    $("#teacherBirthdayEdit").val("");
    $("#teacherStateEdit").val("");
    $("#teacherPhoneEdit").val("");
    $("#teacherEmailEdit").val("");
}

export { FillTable,ClearInputsEditTeacher };
