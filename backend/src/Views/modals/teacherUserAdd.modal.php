<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherUserId" name="teacherUserId" readonly><label for="teacherUserId">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherUserName" name="teacherUserName" readonly><label for="teacherUserName">Nombre del profesor</label></div></div>
</div>
<div class="row g-2 py-3">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherUserAdd" name="teacherUserAdd"><label for="teacherUserAdd">Usuario</label></div><label id="teacherUserAdd-error" class="error text-bg-danger" for="teacherUserAdd"></label><label class="error text-bg-danger userError"></label><label class="error text-bg-success userSuccess"></label></div>
    <div class="col-md"><div class="form-floating input-group"><input type="password" class="form-control" id="teacherUserPass" name="teacherUserPass"><label for="teacherUserPass">Contraseña</label><button class="btn btn-outline-secondary" type="button" id="showPasswordToggle"><i class="bi bi-eye"></i></button></div><label id="teacherUserPass-error" class="error text-bg-danger" for="teacherUserPass"></label></div>
</div>
