function FillTable(response) {
    
   $("#idSubjectDB").val(response.id);
    $("#subjectNameEdit").val(response.name);
    $("#descriptionSubjectEdit").val(response.description);
}

export { FillTable };
