<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherUserIdEdit" name="teacherUserIdEdit" readonly><label for="teacherUserIdEdit">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherUserNameEdit" name="teacherUserNameEdit" readonly><label for="teacherUserNameEdit">Nombre del profesor</label></div></div>
</div>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating input-group"><input type="text" class="form-control" id="teacherUserAddEdit" name="teacherUserAddEdit" readonly><label for="teacherUserAddEdit">Usuario</label><button class="btn btn-outline-secondary" type="button" id="editUserNameteacher"><i class="bi bi-pencil-fill"></i></button></div><label id="teacherUserAddEdit-error" class="error text-bg-danger" for="teacherUserAddEdit"></label><label class="error text-bg-danger userError"></label><label class="error text-bg-success userSuccess"></label></div>
    <div class="col-md"><div class="form-floating input-group"><input type="password" class="form-control" id="teacherUserPassEdit" name="teacherUserPassEdit"><label for="teacherUserPassEdit">Contraseña</label><button class="btn btn-outline-secondary" type="button" id="showPasswordToggleEdit"><i class="bi bi-eye"></i></button></div><label id="teacherUserPassEdit-error" class="error text-bg-danger" for="teacherUserPassEdit"></label></div>
</div>
