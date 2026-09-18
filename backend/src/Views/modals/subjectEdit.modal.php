<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="idSubjectDB" name="idSubjectDB" readonly><label for="idSubjectDB">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectKeyEdit" name="subjectKeyEdit"><label for="subjectKeyEdit">Clave de la materia</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectNameEdit" name="subjectNameEdit"><label for="subjectNameEdit">Nombre de la materia</label></div><label id="subjectNameEdit-error" class="error text-bg-danger" for="subjectNameEdit"></label></div>
</div>
<div class="row g-2 py-1"><div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="descriptionSubjectEdit" name="descriptionSubjectEdit"><label for="descriptionSubjectEdit">Descripción</label></div><label id="descriptionSubjectEdit-error" class="error text-bg-danger" for="descriptionSubjectEdit"></label></div></div>
