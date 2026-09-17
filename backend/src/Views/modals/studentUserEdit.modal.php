<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentUserIdEdit" name="studentUserIdEdit" readonly><label for="studentUserIdEdit">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentUserNameEdit" name="studentUserNameEdit" readonly><label for="studentUserNameEdit">Nombre del alumno</label></div></div>
</div>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating input-group"><input type="text" class="form-control" id="studentUserAddEdit" name="studentUserAddEdit" readonly><label for="studentUserAddEdit">Usuario</label><button class="btn btn-outline-secondary" type="button" id="editUserNameStudent"><i class="bi bi-pencil-fill"></i></button></div><label id="studentUserAddEdit-error" class="error text-bg-danger" for="studentUserAddEdit"></label><label class="error text-bg-danger userError"></label><label class="error text-bg-success userSuccess"></label></div>
    <div class="col-md"><div class="form-floating input-group"><input type="password" class="form-control" id="studentUserPassEdit" name="studentUserPassEdit"><label for="studentUserPassEdit">Contraseña</label><button class="btn btn-outline-secondary" type="button" id="showPasswordToggleEdit"><i class="bi bi-eye"></i></button></div><label id="studentUserPassEdit-error" class="error text-bg-danger" for="studentUserPassEdit"></label></div>
</div>
