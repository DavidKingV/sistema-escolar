<?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    <!--<link rel="stylesheet" href="../assets/css/alumnos.css">-->
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Altas</title>
</head>
<body>

      
    
    <!-- Content -->
    <!-- Content -->
<div id="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Estudiante</h2>
            <a href="../alumnos.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card border-primary shadow">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-user-graduate"></i> Información del Estudiante
            </div>
            <div class="card-body">
                <form id="addStudents">
                    <div class="row">
                        <!-- Nombre completo -->
                        <div class="col-md-6 mb-3">
                            <label for="studentName" class="form-label">
                                <i class="fas fa-id-card"></i> Nombre Completo <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="studentName" name="studentName" required>
                            <div id="userList" class="list-group"></div>
                        </div>

                        <!-- No. Control Interno -->
                        <div class="col-md-6 mb-3">
                            <label for="controlNumber" class="form-label">
                                <i class="fas fa-hashtag"></i> No. Control Interno <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="controlNumber" name="controlNumber" placeholder="123456" required>
                        </div>

                        <!-- No. Control SEP -->
                        <div class="col-md-6 mb-3">
                            <label for="controlSepNumber" class="form-label">
                                <i class="fas fa-hashtag"></i> No. Control SEP <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="controlSepNumber" name="controlSepNumber" placeholder="123456" required>
                        </div>

                        <!-- Usuario Microsoft (oculto por defecto) -->
                        <div class="col-md-12 mb-3" id="microsoftDiv" style="display: none;">
                            <div class="alert alert-success" role="alert">
                                <h5 class="alert-heading">
                                    <i class="fab fa-microsoft"></i> Usuario Microsoft Encontrado
                                </h5>
                                <input type="text" readonly class="form-control-plaintext" id="microsoftId" name="microsoftId">
                                <input type="text" readonly class="form-control-plaintext" id="microsoftEmail" name="microsoftEmail">
                            </div>
                        </div>

                        <!-- Género -->
                        <div class="col-md-6 mb-3">
                            <label for="studentGender" class="form-label">
                                <i class="fas fa-venus-mars"></i> Género <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="studentGender" name="studentGender" required>
                                <option selected value="0">Selecciona</option>
                                <option value="Masculino">Masculino</option>
                                <option value="Femenino">Femenino</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <!-- Fecha nacimiento -->
                        <div class="col-md-6 mb-3">
                            <label for="studentBirthday" class="form-label">
                                <i class="fas fa-birthday-cake"></i> Fecha de Nacimiento <span class="text-danger">*</span>
                            </label>
                            <input type="date" class="form-control" id="studentBirthday" name="studentBirthday" required>
                        </div>

                        <!-- Estado civil -->
                        <div class="col-md-6 mb-3">
                            <label for="studentState" class="form-label">
                                <i class="fas fa-heart"></i> Estado Civil <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="studentState" name="studentState" required>
                                <option selected value="0">Selecciona</option>
                                <option value="Solter@">Solter@</option>
                                <option value="Casad@">Casad@</option>
                                <option value="Divorsiad@">Divorsiad@</option>
                                <option value="Unión Libre">Unión Libre</option>
                                <option value="Viud@">Viud@</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <!-- Nacionalidad -->
                        <div class="col-md-6 mb-3">
                            <label for="studentNation" class="form-label">
                                <i class="fas fa-globe"></i> Nacionalidad <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="studentNation" name="studentNation" required>
                        </div>

                        <!-- CURP -->
                        <div class="col-md-6 mb-3">
                            <label for="studentCurp" class="form-label">
                                <i class="fas fa-id-badge"></i> CURP <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" id="studentCurp" name="studentCurp" required>
                        </div>

                        <!-- Teléfono -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                <i class="fas fa-phone-alt"></i> Número de Teléfono <span class="text-danger">*</span>
                            </label>
                            <div class="phone-group d-flex">
                                <select class="form-select" id="countryCode" style="flex: 0 0 200px;">
                                    <option value="+52">🇲🇽 México (+52)</option>
                                    <option value="+54">🇦🇷 Argentina (+54)</option>
                                    <option value="+57">🇨🇴 Colombia (+57)</option>
                                    <option value="+1">🇺🇸 Estados Unidos (+1)</option>
                                    <option value="+1">🇨🇦 Canadá (+1)</option>
                                </select>
                                <input type="tel" class="form-control" id="studentPhone" name="studentPhone" placeholder="Número de teléfono" required style="flex: 1;">
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="col-md-6 mb-3">
                            <label for="studentEmail" class="form-label">
                                <i class="fas fa-envelope"></i> Correo Electrónico <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="studentEmail" name="studentEmail" required>
                        </div>
                    </div>

                    <!-- Switch de datos extra -->
                    <div class="col-md-12 mt-3">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="noExtraData">
                            <label class="form-check-label" for="noExtraData">
                                <i class="fas fa-info-circle"></i> No se cuenta con todos los datos
                            </label>
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="d-flex justify-content-end mt-4">
                        <button type="reset" class="btn btn-secondary me-2">
                            <i class="fas fa-undo"></i> Limpiar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-save"></i> Guardar Estudiante
                        </button>
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

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/students/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>