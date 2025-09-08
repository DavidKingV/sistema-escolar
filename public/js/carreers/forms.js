function FillTable(response){
    $("#idCarreerDB").val(response.id);

    $("#careerComentsEdit").val(response.description);
}

function ClearInputsEditTeachers(){
    $("#idCarreerDB").val("");
    $("#careerNameEdit").empty();
    $("#carreerAreaEdit").val("");
    $("#careerSubareaEdit").val("");
    $("#careerComentsEdit").val("");

    $("#updateCareer")[0].reset();
    $("#updateCareer").validate().resetForm();
}



export { FillTable, ClearInputsEditTeachers };