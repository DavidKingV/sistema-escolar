<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2">
    <div class="col-md d-none"><div class="form-floating"><input type="text" class="form-control" id="idCarreerDB" name="idCarreerDB" readonly><label for="idCarreerDB">ID</label></div></div>
    <div class="col-md py-1"><div class="form-floating"><select class="form-select" id="careerNameEdit" name="careerNameEdit"><option value="0">Área</option></select><label for="careerNameEdit">Selecciona</label></div><label id="careerNameEdit-error" class="error text-bg-danger" for="careerNameEdit"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="carreerAreaEdit" name="carreerAreaEdit"><label for="carreerAreaEdit">Área</label></div><label id="carreerAreaEdit-error" class="error text-bg-danger" for="carreerAreaEdit"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="careerSubareaEdit" name="careerSubareaEdit"><label for="careerSubareaEdit">Subárea</label></div><label id="careerSubareaEdit-error" class="error text-bg-danger" for="careerSubareaEdit"></label></div>
</div>
<div class="row g-2 py-1"><div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="careerComentsEdit" name="careerComentsEdit"><label for="careerComentsEdit">Comentarios</label></div><label id="careerComentsEdit-error" class="error text-bg-danger" for="careerComentsEdit"></label></div></div>
