//llenado de los inputs para edicion
function FillTable(response){
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

export { FillTable };