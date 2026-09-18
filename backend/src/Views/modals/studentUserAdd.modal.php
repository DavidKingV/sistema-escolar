<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2 py-3">
    <div class="col-md d-none"><div class="form-floating"><input type="text" class="form-control" id="studentUserId" name="studentUserId" readonly><label for="studentUserId">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentUserName" name="studentUserName" readonly><label for="studentUserName">Nombre del alumno</label></div></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentUserAdd" name="studentUserAdd"><label for="studentUserAdd">Usuario</label></div><label id="studentUserAdd-error" class="error text-bg-danger" for="studentUserAdd"></label><label class="error text-bg-danger userError"></label><label class="error text-bg-success userSuccess"></label></div>
    <div class="col-md"><div class="form-floating input-group"><input type="password" class="form-control" id="studentUserPass" name="studentUserPass"><label for="studentUserPass">Contraseña</label><button class="btn btn-outline-secondary" type="button" id="showPasswordToggle"><i class="bi bi-eye"></i></button></div><label id="studentUserPass-error" class="error text-bg-danger" for="studentUserPass"></label></div>
</div>
