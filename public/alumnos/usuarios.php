<?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
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
    <title>Usuaros de alumnos</title>
</head>
<body>
    <div id="globalLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(33,37,41,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </div>
      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Usuarios de alumnos</h2>
                <a href="altas.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Agregar Estudiante
                </a>
            </div>

            <div class="card border-primary shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-graduate"></i> Información del Estudiante
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="studentsUsersTable">
                            <thead>
                                <tr>
                                    <!--<th class="text-center">ID</th>-->
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Usuario</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                    <th class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Datos dinámicos aquí -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="card border-primary shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-graduate"></i> Alumnos Con Cuenta Microsoft
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="studentsMicrosoftUsersTable">
                            <thead>
                                <tr>
                                    <th class="text-center">ID</th>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">Email</th>
                                    <th class="text-center">Estado</th>
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

<!-- Modal -->
<div class="modal fade modal-lg" id="StutentUserModal" tabindex="-1" aria-labelledby="StutentUserModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="StutentUserModalTitle">Alta de Usuario Local</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addStudentsUsers">
            <div class="row g-2 py-3">            
                <div class="col-md hidden">
                    <div class="form-floating">
                    <input type="text" class="form-control hidden" id="studentUserId" name="studentUserId" readonly>
                    <label for="studentUserId">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentUserName" name="studentUserName" readonly>
                    <label for="studentUserName">Nombre del alumno</label>
                    </div>                   
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentUserAdd" name="studentUserAdd" value="">
                    <label for="studentUserAdd">Usuario</label>
                    </div>
                    <label id="studentUserAdd-error" class="error text-bg-danger" for="studentUserAdd" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <div>
                        <p class="">                                                
                        <label class="error text-bg-danger userError" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        <label class="error text-bg-success userSuccess" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>    
                        </p>
                    </div>                                         
                </div>
                <div class="col-md">
                    <div class="form-floating input-group">
                        <input type="password" class="form-control" id="studentUserPass" name="studentUserPass" value="">
                        <label for="studentUserPass">Contraseña</label>
                        <button class="btn btn-outline-secondary" type="button" id="showPasswordToggle">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <div>
                        <p class="">                                                                        
                        <label id="studentUserPass-error" class="error text-bg-danger" for="studentUserPass" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                        </p>
                    </div> 
                </div>        
            </div>        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="submitUser" class="btn btn-primary">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal fade modal-lg" id="StutentUserEditModal" tabindex="-1" aria-labelledby="StutentUserEditModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="StutentUserEditModalTitle">Editar Usuario</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editStudentsUsers">
            <div class="row g-2 py-3">            
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentUserIdEdit" name="studentUserIdEdit" readonly>
                    <label for="studentUserIdEdit">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentUserNameEdit" name="studentUserNameEdit" readonly>
                    <label for="studentUserNameEdit">Nombre del alumno</label>
                    </div>                   
                </div>
            </div>
            <div class="row g-2 py-3">
                <div class="col-md">
                    <div class="form-floating input-group">
                    <input type="text" class="form-control" id="studentUserAddEdit" name="studentUserAddEdit" value="" readonly>
                    <label for="studentUserAddEdit">Usuario</label>
                    <button class="btn btn-outline-secondary" type="button" id="editUserNameStudent">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    </div>
                    <label id="studentUserAddEdit-error" class="error text-bg-danger" for="studentUserAddEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-danger userError" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-success userSuccess" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating input-group">
                    <input type="password" class="form-control" id="studentUserPassEdit" name="studentUserPassEdit" value="">
                    <label for="studentUserPassEdit">Contraseña</label>
                    <button class="btn btn-outline-secondary" type="button" id="showPasswordToggleEdit">
                        <i class="bi bi-eye"></i>
                    </button>
                    </div>
                    <label id="studentUserPassEdit-error" class="error text-bg-danger" for="studentUserPassEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>                
            </div>        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="submitEditUserStudent" class="btn btn-primary">Guardar</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Microsoft-->
<div class="modal fade modal-lg" id="toMicrosoftModal" tabindex="-1" aria-labelledby="toMicrosoftModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="toMicrosoftModalTitle">Asociar cuenta Microsoft</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="toMicrosoftStudentUser">
            <div class="row g-2 py-3">            
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="studentUserIdMicrosoft" name="studentUserIdMicrosoft" readonly>
                    <label for="studentUserIdMicrosoft">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating input-group">
                        <input type="text" class="form-control" id="studentUserNameMicrosoft" name="studentUserNameMicrosoft">
                        <label for="studentUserNameMicrosoft">Nombre del alumno</label>
                        <button class="btn btn-outline-success" type="button" id="searchMicrosoftUser">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>                   
                </div>                
            </div>            
        </form>

        <div class="mt-3" id="microsoftUserSearchResults"></div>
      </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
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
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/students/index.js"></script>
<script type="module" src="../js/students/users/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>