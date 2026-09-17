<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="idMainSubjectInfo" name="idMainSubjectInfo" readonly><label for="idMainSubjectInfo">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="idChildSubjectInfo" name="idChildSubjectInfo" readonly><label for="idChildSubjectInfo">ID Submateria</label></div></div>
</div>
<div class="row g-2 py-4">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectChildNameInfo" name="subjectChildNameInfo"><label for="subjectChildNameInfo">Nombre de la submateria</label></div><label id="subjectChildNameInfo-error" class="error text-bg-danger" for="subjectChildNameInfo"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="descriptionChildSubjectInfo" name="descriptionChildSubjectInfo"><label for="descriptionChildSubjectInfo">Descripción</label></div><label id="descriptionChildSubjectInfo-error" class="error text-bg-danger" for="descriptionChildSubjectInfo"></label></div>
</div>
