<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<form id="toMicrosoftStudentUser">
    <div class="row g-2 py-3">
        <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentUserIdMicrosoft" name="studentUserIdMicrosoft" readonly><label for="studentUserIdMicrosoft">ID</label></div></div>
        <div class="col-md"><div class="form-floating input-group"><input type="text" class="form-control" id="studentUserNameMicrosoft" name="studentUserNameMicrosoft"><label for="studentUserNameMicrosoft">Nombre del alumno</label><button class="btn btn-outline-success" type="button" id="findMicrosoftUser"><i class="bi bi-search"></i></button></div></div>
    </div>
</form>
<div class="mt-3" id="microsoftUserSearchResults"></div>
