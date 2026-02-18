<?php include_once __DIR__.'/../backend/views/mainMenu.php';?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/allMain.min.css">
    <link rel="stylesheet" href="assets/css/alumnos.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Alumnos</title>
</head>
<body>
<!-- Content -->
<div id="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Lista de Estudiantes</h2>
            <a href="alumnos/altas.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Agregar Estudiante
            </a>
        </div>
        <div class="card">
            <div class="card-header">
                <i class="fas fa-users"></i> Estudiantes Registrados
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="studentTable">
                        <thead>
                            <tr>
                                <th class="text-center">Nombre</th>
                                <th class="text-center">No. Control</th>
                                <th class="text-center">Programa</th>
                                <th class="text-center">Clave Grupo</th>
                                <th class="text-center">Teléfono</th>
                                <th class="text-center">Estatus</th>
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
</html>

<!-- Modal -->
<div class="modal fade modal-lg" id="statusModal" tabindex="-1" aria-labelledby="statusModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="statusModalLabel">Editar estatus del alumno</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="edit_paciente">
      <div class="modal-body" id="statusModalBody">
        
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal EDIT -->
<div class="modal fade modal-lg" id="StutentEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="StutentEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="StutentEditModalLabel">Editar Alumno</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateStudent">
            <div class="row g-2">
                <div class="col-md hidden">
                    <div class="form-floating">
                    <input type="text" class="form-control hidden" id="idStudentDB" name="idStudentDB" readonly>
                    <label for="idStudentDB">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="controlNumber" name="controlNumber" value="">
                    <label for="controlNumber">No. Control</label>
                    </div>
                    <div>
                        <p class="py-1">
                        <label id="controlNumber-error" class="error text-bg-danger" for="controlNumber" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div>                    
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="controlSepNumber" name="controlSepNumber">
                    <label for="controlSepNumber">No. Control SEP</label>
                    </div>
                    <div>
                        <p class="py-1">
                        <label id="controlSepNumber-error" class="error text-bg-danger" for="controlSepNumber" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div>                    
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentName" name="studentName" value="">
                    <label for="studentName">Nombre del Alumno</label>
                    </div>
                    <div>
                        <p class="py-1">
                        <label id="studentName-error" class="error text-bg-danger" for="studentName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>                    
                        </p>
                    </div>                    
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                        <select class="form-select" id="studentGender" name="studentGender">
                            <option selected value="0">Genero</option>
                            <option value="Masculino">Masculino</option>
                            <option value="Femenino">Femenino</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <label for="studentGender">Selecciona</label>
                    </div>
                    <div>
                        <p class="py-1">                        
                        <label id="studentGender-error" class="error text-bg-danger" for="studentGender" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div> 
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="date" class="form-control" id="studentBirthday" name="studentBirthday" value="">
                    <label for="studentBirthday">Fecha de nacimiento</label>
                    </div>
                    <div>
                        <p class="py-1">                                                
                        <label id="studentBirthday-error" class="error text-bg-danger" for="studentBirthday" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div> 
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                        <select class="form-select" id="studentState" name="studentState">
                            <option selected value="0">Estado civil</option>
                            <option value="Solter@">Solter@</option>
                            <option value="Casad@">Casad@</option>
                            <option value="Divorsiad@">Divorsiad@</option>
                            <option value="Unión Libre">Unión Libre</option>
                            <option value="Viud@">Viud@</option>
                            <option value="Otro">Otro</option>
                        </select>
                        <label for="studentState">Selecciona</label>
                    </div>
                    <div>
                        <p class="py-1">                        
                            <label id="studentState-error" class="error text-bg-danger" for="studentState" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>                        
                        </p>
                    </div> 
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentNation" name="studentNation" value="">
                    <label for="studentNation">Nacionalidad</label>
                    </div>
                    <div>
                        <p class="py-1">                                                
                        <label id="studentNation-error" class="error text-bg-danger" for="studentNation" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>                        
                        </p>
                    </div> 
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentCurp" name="studentCurp" value="">
                    <label for="studentCurp">CURP</label>
                    </div>
                    <div>
                        <p class="py-1">                                                                                                
                        <label id="studentCurp-error" class="error text-bg-danger" for="studentCurp" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div> 
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentPhone" name="studentPhone" value="">
                    <label for="studentPhone">Teléfono</label>
                    </div>
                    <label id="studentPhone-error" class="error text-bg-danger" for="studentPhone" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentEmail" name="studentEmail" value="">
                    <label for="studentEmail">Email</label>
                    </div>
                    <div>
                        <p class="py-1">                                                                                                                        
                        <label id="studentEmail-error" class="error text-bg-danger" for="studentEmail" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div> 
                </div>
            </div>
            </div>        
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
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
<script type="module" src="js/students/index.js?v=1.1"></script>
<!--<script type="module" src="public/js/students.js"></script>-->
<script type="module" src="js/utils/sessions.js"></script>