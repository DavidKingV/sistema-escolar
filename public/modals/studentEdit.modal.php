<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2">
    <div class="col-md d-none"><div class="form-floating"><input type="text" class="form-control" id="idStudentDB" name="idStudentDB" readonly><label for="idStudentDB">ID</label></div></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="controlNumber" name="controlNumber"><label for="controlNumber">No. Control</label></div><label id="controlNumber-error" class="error text-bg-danger" for="controlNumber"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="controlSepNumber" name="controlSepNumber"><label for="controlSepNumber">No. Control SEP</label></div><label id="controlSepNumber-error" class="error text-bg-danger" for="controlSepNumber"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentName" name="studentName"><label for="studentName">Nombre del alumno</label></div><label id="studentName-error" class="error text-bg-danger" for="studentName"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><select class="form-select" id="studentGender" name="studentGender"><option value="0">Género</option><option value="Masculino">Masculino</option><option value="Femenino">Femenino</option><option value="Otro">Otro</option></select><label for="studentGender">Selecciona</label></div><label id="studentGender-error" class="error text-bg-danger" for="studentGender"></label></div>
    <div class="col-md"><div class="form-floating"><input type="date" class="form-control" id="studentBirthday" name="studentBirthday"><label for="studentBirthday">Fecha de nacimiento</label></div><label id="studentBirthday-error" class="error text-bg-danger" for="studentBirthday"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><select class="form-select" id="studentState" name="studentState"><option value="0">Estado civil</option><option value="Solter@">Solter@</option><option value="Casad@">Casad@</option><option value="Divorsiad@">Divorsiad@</option><option value="Unión Libre">Unión Libre</option><option value="Viud@">Viud@</option><option value="Otro">Otro</option></select><label for="studentState">Selecciona</label></div><label id="studentState-error" class="error text-bg-danger" for="studentState"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentNation" name="studentNation"><label for="studentNation">Nacionalidad</label></div><label id="studentNation-error" class="error text-bg-danger" for="studentNation"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentCurp" name="studentCurp"><label for="studentCurp">CURP</label></div><label id="studentCurp-error" class="error text-bg-danger" for="studentCurp"></label></div>
</div>
<div class="row g-2 py-1">
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentPhone" name="studentPhone"><label for="studentPhone">Teléfono</label></div><label id="studentPhone-error" class="error text-bg-danger" for="studentPhone"></label></div>
    <div class="col-md"><div class="form-floating"><input type="text" class="form-control" id="studentEmail" name="studentEmail"><label for="studentEmail">Correo electrónico</label></div><label id="studentEmail-error" class="error text-bg-danger" for="studentEmail"></label></div>
</div>
