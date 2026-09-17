<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="idMainSubject" name="idMainSubject" readonly><label for="idMainSubject">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="carrerId" name="carrerId" readonly><label for="carrerId">ID Carrera</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectManinName" name="subjectManinName" readonly><label for="subjectManinName">Materia padre</label></div></div>
</div>
<div class="row g-2 py-4">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectChildKey" name="subjectChildKey"><label for="subjectChildKey">Clave de la materia</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="subjectChildName" name="subjectChildName"><label for="subjectChildName">Nombre de la submateria</label></div><label id="subjectChildName-error" class="error text-bg-danger" for="subjectChildName"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="descriptionChildSubject" name="descriptionChildSubject"><label for="descriptionChildSubject">Descripción</label></div><label id="descriptionChildSubject-error" class="error text-bg-danger" for="descriptionChildSubject"></label></div>
</div>
