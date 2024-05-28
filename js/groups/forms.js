function FillTable(response) {
    // Fill the form with the data
    $("#idGroupDB").val(response.id);
    $("#idCarreerHidden").val(response.id_carreer);
    $("#carreerNameGroupEdit").val(response.carreer_name);
    $("#keyGroupEdit").val(response.key);
    $("#nameGroupEdit").val(response.name);

    var birthdateISO = response.startDate.split("/").reverse().join("-");
    $("#startDateEdit").val(birthdateISO);
    var birthdateISO = response.endDate.split("/").reverse().join("-");
    $("#endDateEdit").val(birthdateISO);

    $("#descriptionGroupEdit").val(response.description);
    
}

function CleanInputsGroupsEdit(){
    $("#idGroupDB").val("");
    $("#idCarreerHidden").val("");
    $("#carreerNameGroupEdit").val("");
    $("#keyGroupEdit").val("");
    $("#nameGroupEdit").val("");
    $("#startDateEdit").val("");
    $("#endDateEdit").val("");
    $("#descriptionGroupEdit").val("");

    $("#updateGroup")[0].reset();
    $("#updateGroup").validate().resetForm();
}

export { FillTable, CleanInputsGroupsEdit };