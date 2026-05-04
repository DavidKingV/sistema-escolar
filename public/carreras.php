<?php include_once __DIR__.'/../backend/views/mainMenu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/allMain.min.css">
    <link rel="stylesheet" href="assets/css/carreers.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Alumnos</title>
</head>
<body>

      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de Carreras</h2>
                <a href="carreras/altas.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Agregar Carrera
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Estudiantes Registrados
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="carreersTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Area</th>
                                    <th class="text-center">Subarea</th>
                                    <th class="text-center">Materias</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Datos dinámicos aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!-- Modal EDIT -->
<div class="modal fade modal-lg" id="CareerEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="CareerEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="CareerEditModalLabel">Editar Carrera</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateCareer">
            <div class="row g-2">
                <div class="col-md hidden">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="idCarreerDB" name="idCarreerDB" readonly>
                    <label for="idCarreerDB">ID</label>
                    </div>
                </div>                
                <div class="col-md py-1">
                    <div class="form-floating">
                    <select class="form-select" id="careerNameEdit" name="careerNameEdit"  aria-label="Floating label select example">
                        <option selected value="0">Área</option>   
                    </select>
                    <label for="floatingSelect">Selecciona</label>
                    </div>
                    <label id="careerNameEdit-error" class="error text-bg-danger" for="careerNameEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>                    
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="carreerAreaEdit" name="carreerAreaEdit" value="">
                    <label for="carreerAreaEdit">Área</label>
                    </div>
                    <label id="carreerAreaEdit-error" class="error text-bg-danger" for="carreerAreaEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="careerSubareaEdit" name="careerSubareaEdit" value="">
                    <label for="careerSubareaEdit">Subarea</label>
                    </div>
                    <label id="careerSubareaEdit-error" class="error text-bg-danger" for="careerSubareaEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>                
            </div>   
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="careerComentsEdit" name="careerComentsEdit" value="">
                    <label for="careerComentsEdit">Comentarios</label>
                    </div>
                    <label id="careerComentsEdit-error" class="error text-bg-danger" for="careerComentsEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
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

<!-- Modal materias -->
<div class="modal fade modal-xl modal-dialog-scrollable" id="subjectsModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="subjectsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="subjectsModalLabel">Materias de la carrera</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body" id="subjectsModalBody">

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="js/carreers/index.js"></script>
<script type="module" src="js/carreers/carreers.js"></script>
<script type="module" src="js/careers.js"></script>
<script type="module" src="js/utils/sessions.js"></script>