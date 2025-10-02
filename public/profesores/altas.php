<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <!--<link rel="stylesheet" href="../assets/css/alumnos.css">-->
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Agregar Profesor</title>
</head>
<body>

    <?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Agregar Profesor</h2>
                <a href="../profesores.php" class="btn btn-secondary">
                    <i class="bi bi-person-badge"></i> Volver a la lista
                </a>
            </div>
            <div class="card border-primary shadow">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-person-fill"></i> Información del Profesor
                </div>
                <div class="card-body">
                    <form id="addTeachers">
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <label for="teacherName" class="form-label">Nombre Completo</label>
                                <label id="teacherName-error" class="error text-bg-danger" for="teacherName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <input type="text" class="form-control" id="teacherName" name="teacherName">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <label for="teacherGender" class="form-label">Genero</label>
                                <label id="teacherGender-error" class="error text-bg-danger" for="teacherGender" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <select class="form-select" id="teacherGender" name="teacherGender">
                                    <option selected value="0">Selecciona</option>
                                    <option value="Masculino">Masculino</option>
                                    <option value="Femenino">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                            <div class="col-md py-3">
                                <label for="teacherBirthday" class="form-label">Fecha de nacimiento</label>
                                <label id="teacherBirthday-error" class="error text-bg-danger" for="teacherBirthday" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <input type="date" class="form-control" id="teacherBirthday" name="teacherBirthday">
                            </div>
                            <div class="col-md py-3">
                                <label for="teacherState" class="form-label">Estado Civil</label>
                                <label id="teacherState-error" class="error text-bg-danger" for="teacherState" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <select class="form-select" id="teacherState" name="teacherState">
                                    <option selected value="0">Selecciona</option>
                                    <option value="Solter@">Solter@</option>
                                    <option value="Casad@">Casad@</option>
                                    <option value="Divorsiad@">Divorsiad@</option>
                                    <option value="Unión Libre">Unión Libre</option>
                                    <option value="Viud@">Viud@</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <label for="teacherPhone" class="form-label">Teléfono</label>
                                <label id="teacherPhone-error" class="error text-bg-danger" for="teacherPhone" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <input type="text" class="form-control" id="teacherPhone" name="teacherPhone">
                            </div>
                            <div class="col-md py-3">
                                <label for="teacherEmail" class="form-label">Email</label>
                                <label id="teacherEmail-error" class="error text-bg-danger" for="teacherEmail" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <input type="mail" class="form-control" id="teacherEmail" name="teacherEmail">
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>


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
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/teachers/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>