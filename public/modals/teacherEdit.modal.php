<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2 py-4">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="idTeacherEdit" name="idTeacherEdit" readonly><label for="idTeacherEdit">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherNameEdit" name="teacherNameEdit"><label for="teacherNameEdit">Nombre del profesor</label></div></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><select class="form-select" id="teacherGenderEdit" name="teacherGenderEdit"><option value="0">Género</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option><option value="Otro">Otro</option></select><label for="teacherGenderEdit">Selecciona</label></div><label id="teacherGenderEdit-error" class="error text-bg-danger" for="teacherGenderEdit"></label></div>
    <div class="col-md"><div class="form-floating"><input type="date" class="form-control" id="teacherBirthdayEdit" name="teacherBirthdayEdit"><label for="teacherBirthdayEdit">Fecha de nacimiento</label></div><label id="teacherBirthdayEdit-error" class="error text-bg-danger" for="teacherBirthdayEdit"></label></div>
    <div class="col-md"><div class="form-floating"><select class="form-select" id="teacherStateEdit" name="teacherStateEdit"><option value="0">Estado civil</option><option value="Solter@">Solter@</option><option value="Casad@">Casad@</option><option value="Divorsiad@">Divorsiad@</option><option value="Unión Libre">Unión Libre</option><option value="Viud@">Viud@</option><option value="Otro">Otro</option></select><label for="teacherStateEdit">Selecciona</label></div><label id="teacherStateEdit-error" class="error text-bg-danger" for="teacherStateEdit"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherPhoneEdit" name="teacherPhoneEdit"><label for="teacherPhoneEdit">Teléfono</label></div><label id="teacherPhoneEdit-error" class="error text-bg-danger" for="teacherPhoneEdit"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="teacherEmailEdit" name="teacherEmailEdit"><label for="teacherEmailEdit">Correo electrónico</label></div><label id="teacherEmailEdit-error" class="error text-bg-danger" for="teacherEmailEdit"></label></div>
</div>
