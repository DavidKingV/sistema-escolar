<?php
require __DIR__.'/../backend/vendor/autoload.php';
include __DIR__.'/../backend/views/mainMenu.php';

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\PermissionHelper;

$VerifySession = auth::check();

$isAdmin       = $VerifySession['isAdmin']       ?? false;
$userPerms     = $VerifySession['permissions']   ?? [];

if (!PermissionHelper::canAccess('manage_teachers', $userPerms, $isAdmin)) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/allMain.min.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Profesores</title>
</head>
<body>

    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de Profesores</h2>
                <a href="profesores/altas.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Agregar Profesor
                </a>
            </div>
            <div class="card mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Lista completa</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="teachersTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Teléfono</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se llenarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!-- Modal edit -->
<div class="modal fade modal-lg" id="TeacherEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="TeacherEditModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="TeacherEditModalLabel">Editar Alumno</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body
        ">
            <form id="editTeacherForm">
                <div class="row g-2 py-4">
                    <div class="col-md">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="idTeacherEdit" name="idTeacherEdit" readonly>
                        <label for="idTeacherEdit">ID</label>
                        </div>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="teacherNameEdit" name="teacherNameEdit" value="">
                        <label for="teacherNameEdit">Nombre del Profesor</label>
                        </div>
                    </div>
                </div>
                <div class="row g-2 py-1">
                    <div class="col-md">
                        <div class="form-floating">
                            <select class="form-select" id="teacherGenderEdit" name="teacherGenderEdit">
                                <option selected value="0">Genero</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <label for="teacherGenderEdit">Selecciona</label>
                        </div>
                        <label id="teacherGenderEdit-error" class="error text-bg-danger" for="teacherGenderEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <input type="date" class="form-control" id="teacherBirthdayEdit" name="teacherBirthdayEdit" value="">
                            <label for="teacherBirthdayEdit">Fecha de nacimiento</label>
                        </div>
                        <label id="teacherBirthdayEdit-error" class="error text-bg-danger" for="teacherBirthdayEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                            <select class="form-select" id="teacherStateEdit" name="teacherStateEdit">
                                <option selected value="0">Estado civil</option>
                                <option value="Solter@">Solter@</option>
                                <option value="Casad
                                @">Casad@</option>
                                <option value="Divorsiad@">Divorsiad@</option>
                                <option value="Unión Libre">Unión Libre</option>
                                <option value="Viud@">Viud@</option>
                                <option value="Otro">Otro</option>
                            </select>
                            <label for="teacherStateEdit">Selecciona</label>
                        </div>
                        <label id="teacherStateEdit-error" class="error text-bg-danger" for="teacherStateEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    </div>
                </div>                
                <div class="row g-2 py-1">
                    <div class="col-md">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="teacherPhoneEdit" name="teacherPhoneEdit" value="">
                        <label for="teacherPhoneEdit">Teléfono</label>
                        </div>
                        <label id="teacherPhoneEdit-error" class="error text-bg-danger" for="teacherPhoneEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    </div>
                    <div class="col-md">
                        <div class="form-floating">
                        <input type="text" class="form-control" id="teacherEmailEdit" name="teacherEmailEdit" value="">
                        <label for="teacherEmailEdit">Email</label>
                        </div>
                        <label id="teacherEmailEdit-error" class="error text-bg-danger" for="teacherEmailEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    </div>
                </div>
                </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
        </div>
    </div>
    </div>
</div>


<!-- Boostrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js" integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>

<!-- datables -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.js"></script>

<!-- globaljs -->
<script src="js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="js/teachers/index.js"></script>
<script type="module" src="js/utils/sessions.js"></script>