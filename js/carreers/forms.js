function FillTable(response){
    $("#idCarreerDB").val(response.id);

    $("#careerComents").val(response.description);
}

export { FillTable };