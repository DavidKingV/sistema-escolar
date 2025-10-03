<?php include __DIR__.'/../backend/views/mainMenu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/allMain.min.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">   
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <!--<link rel="stylesheet" href="assets/css/dashboard.css">-->
    <title>Materias</title>
</head>
<body>

      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de Materias</h2>
                <a href="materias/altas.php" class="btn btn-primary">
                    <i class="bi bi-plus"></i> Agregar Materia
                </a>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-book"></i> Materias Registradas
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="subjectsTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Carrera</th>
                                    <th class="text-center">Descripción</th>
                                    <th class="text-center">Materias Hijas</th>
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

<!-- Modal EDIT -->
<div class="modal fade modal-lg" id="SubjectsEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="SubjectsEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="SubjectsEditModalLabel">Editar Materia</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateSubject">
            <div class="row g-2">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="idSubjectDB" name="idSubjectDB" readonly>
                    <label for="idSubjectDB">ID</label>
                    </div>                    
                </div> 
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="subjectKeyEdit" name="subjectKeyEdit">
                        <label for="subjectKeyEdit" class="form-label">Clave de la Materia</label>
                    </div>
                </div>                  
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="subjectNameEdit" name="subjectNameEdit" value="">
                    <label for="subjectNameEdit">Nombre de la Materia</label>
                    </div>
                    <label id="subjectNameEdit-error" class="error text-bg-danger" for="subjectNameEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>                
            </div>            
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="descriptionSubjectEdit" name="descriptionSubjectEdit" value="">
                    <label for="descriptionSubjectEdit">Descripción</label>
                    </div>
                    <label id="descriptionSubjectEdit-error" class="error text-bg-danger" for="descriptionSubjectEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
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

<!-- Modal ADD CHILD -->
<div class="modal fade modal-lg" id="SubjectsChildAddModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="SubjectsChildAddModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="SubjectsChildAddModalLabel">Agregar Submateria</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addSubjectChild">
            <div class="row g-2">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="idMainSubject" name="idMainSubject" readonly>
                    <label for="idMainSubject">ID</label>
                    </div>                    
                </div>  
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="carrerId" name="carrerId" value="" readonly>
                    <label for="carrerId">ID Carrera</label>
                    </div>
                </div>              
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="subjectManinName" name="subjectManinName" value="" readonly>
                    <label for="subjectManinName">Nombre de la Materia Padre</label>
                    </div>                    
                </div>                
            </div>            
            <div class="row g-2 py-4">
                <div class="col-md">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="subjectChildKey" name="subjectChildKey">
                        <label for="subjectChildKey" class="form-label">Clave de la Materia</label>
                    </div>
                </div>   
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="subjectChildName" name="subjectChildName" value="">
                    <label for="subjectChildName">Nombre de la Submateria</label>
                    </div>
                    <div>
                        <p class="py-1">                        
                        <label id="subjectChildName-error" class="error text-bg-danger" for="subjectChildName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div>    
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="descriptionChildSubject" name="descriptionChildSubject" value="">
                    <label for="descriptionChildSubject">Descripción</label>
                    </div>
                    <div>
                        <p class="py-1">                            
                            <label id="descriptionChildSubject-error" class="error text-bg-danger" for="descriptionChildSubject" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div>    
                </div>                
            </div>
            </div>        
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
            </div>
    </div>
  </div>
</div>

<!-- Modal CHILD INFO-->
<div class="modal fade modal-lg" id="childSubjectsModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="childSubjectsModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="childSubjectsModalLabel">Materia Hija</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="subjectChildInfo">
          <div class="row g-2">
            <div class="col-md">
              <div class="form-floating">
                <input type="text" class="form-control" id="idMainSubjectInfo" name="idMainSubjectInfo" readonly>
                <label for="idMainSubjectInfo">ID</label>
              </div>                    
            </div>
            <div class="col-md">
              <div class="form-floating">
                <input type="text" class="form-control" id="idChildSubjectInfo" name="idChildSubjectInfo" readonly>
                <label for="idChildSubjectInfo">ID Submateria</label>
              </div>                    
            </div>                                    
          </div>            
          <div class="row g-2 py-4">
            <div class="col-md">
              <div class="form-floating">
                <input type="text" class="form-control" id="subjectChildNameInfo" name="subjectChildNameInfo" value="">
                <label for="subjectChildNameInfo">Nombre de la Submateria</label>
              </div>
              <div>
                <p class="py-1">                        
                  <label id="subjectChildNameInfo-error" class="error text-bg-danger" for="subjectChildNameInfo" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                </p>
              </div>    
            </div>
            <div class="col-md">
              <div class="form-floating">
                <input type="text" class="form-control" id="descriptionChildSubjectInfo" name="descriptionChildSubjectInfo" value="">
                <label for="descriptionChildSubjectInfo">Descripción</label>
              </div>
              <div>
                <p class="py-1">                            
                  <label id="descriptionChildSubjectInfo-error" class="error text-bg-danger" for="descriptionChildSubjectInfo" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                </p>
              </div>    
            </div>                
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" id="updateSubjectChild" class="btn btn-primary">Actualizar</button>
            <button type="button" id="deleteSubjectChild" class="btn btn-danger">Eliminar</button>
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

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="js/subjects/index.js"></script>
<script type="module" src="js/utils/sessions.js"></script>