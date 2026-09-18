function FillTable(response) {
  $("#idSubjectDB").val(response.id);
  $("#subjectKeyEdit").val(response.clave);
  $("#subjectNameEdit").val(response.name);
  $("#descriptionSubjectEdit").val(response.description);
}

function FillChildInfo(response) {
  $("#idMainSubjectInfo").val(response.id);
  $("#idChildSubjectInfo").val(response.id_subject);
  $("#subjectChildNameInfo").val(response.name);
  $("#descriptionChildSubjectInfo").val(response.description);
}

function FillChildForm(response) {
  $("#idChildDB").val(response.id);
  $("#childNameEdit").val(response.name);
  $("#childLastNameEdit").val(response.lastName);
  $("#childAgeEdit").val(response.age);
  $("#childGenderEdit").val(response.data);
}

function ClearSubjectChildInputs() {
  $("#idMainSubject").val("");
  $("#subjectManinName").val("");
  $("#subjectChildName").val("");
  $("#subjectChildDescription").val("");

  $("#addSubjectChild")[0].reset();
  $("#addSubjectChild").validate().resetForm();
}

export { FillTable, FillChildInfo, ClearSubjectChildInputs };
