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
    <title>Usuarios de Profesores</title>
</head>
<body>

<div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de Profesores</h2>
                <a href="profesores/altas.php" class="btn btn-primary">
                    <i class="bi bi-person-fill-up"></i> Agregar Profesor
                </a>
            </div>
                <div class="card border-primary shadow">
                    <div class="card-header bg-primary text-white">
                    <i class="fas fa-user"></i> Lista de usuarios
                </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="teacherUsersTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Usuario</th>
                                        <th class="text-center">Estado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    
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
<div class="modal fade modal-lg" id="teacherUserModal" tabindex="-1" aria-labelledby="teacherUserModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="teacherUserModalTitle">Alta de Usuario</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="addTeachersUsers">
            <div class="row g-2 py-3">            
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="teacherUserId" name="teacherUserId" readonly>
                    <label for="teacherUserId">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="teacherUserName" name="teacherUserName" readonly>
                    <label for="teacherUserName">Nombre del alumno</label>
                    </div>                   
                </div>
            </div>
            <div class="row g-2 py-3">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="teacherUserAdd" name="teacherUserAdd" value="">
                    <label for="teacherUserAdd">Usuario</label>
                    </div>
                    <label id="teacherUserAdd-error" class="error text-bg-danger" for="teacherUserAdd" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-danger userError" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-success userSuccess" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating input-group">
                        <input type="password" class="form-control" id="teacherUserPass" name="teacherUserPass" value="">
                        <label for="teacherUserPass">Contraseña</label>
                        <button class="btn btn-outline-secondary" type="button" id="showPasswordToggle">
                            <i class="bi bi-eye"></i>
                        </button>
                    </div>
                    <label id="teacherUserPass-error" class="error text-bg-danger" for="teacherUserPass" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
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
<div class="modal fade modal-lg" id="TeacherUserEditModal" tabindex="-1" aria-labelledby="TeacherUserEditModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="TeacherUserEditModalTitle">Editar Usuario</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="editTeachersUsers">
            <div class="row g-2 py-3">            
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="teacherUserIdEdit" name="teacherUserIdEdit" readonly>
                    <label for="teacherUserIdEdit">ID</label>
                    </div>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="teacherUserNameEdit" name="teacherUserNameEdit" readonly>
                    <label for="teacherUserNameEdit">Nombre del alumno</label>
                    </div>                   
                </div>
            </div>
            <div class="row g-2 py-3">
                <div class="col-md">
                    <div class="form-floating input-group">
                    <input type="text" class="form-control" id="teacherUserAddEdit" name="teacherUserAddEdit" value="" readonly>
                    <label for="teacherUserAddEdit">Usuario</label>
                    <button class="btn btn-outline-secondary" type="button" id="editUserNameteacher">
                    <i class="bi bi-pencil-fill"></i>
                    </button>
                    </div>
                    <label id="teacherUserAddEdit-error" class="error text-bg-danger" for="teacherUserAddEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-danger userError" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                    <label class="error text-bg-success userSuccess" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating input-group">
                    <input type="password" class="form-control" id="teacherUserPassEdit" name="teacherUserPassEdit" value="">
                    <label for="teacherUserPassEdit">Contraseña</label>
                    <button class="btn btn-outline-secondary" type="button" id="showPasswordToggleEdit">
                        <i class="bi bi-eye"></i>
                    </button>
                    </div>
                    <label id="teacherUserPassEdit-error" class="error text-bg-danger" for="teacherUserPassEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>                
            </div>        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="submitEditUserTeacher" class="btn btn-primary">Guardar</button>
      </div>
      </form>
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
<script type="module" src="../js/teachers/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>