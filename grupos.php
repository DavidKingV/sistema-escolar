<?php
require_once(__DIR__.'/php/vendor/autoload.php');

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Vendor\Schoolarsystem\MicrosoftActions;
use Vendor\Schoolarsystem\loadEnv;

session_start();

loadEnv::cargar();
$VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);

$dbConnection = new DBConnection();
$connection = $dbConnection->getConnection();

if (!$VerifySession['success']) {
    header('Location: index.php?sesion=expired');
    exit();
}else{
    $userId = $VerifySession['userId'] ?? NULL;
    $accessToken = $VerifySession['accessToken']?? NULL;
    $admin = $VerifySession['admin'];

    $userName='';
    $userPhoto='';

    if($userId !== NULL && $accessToken != NULL && $admin == true){
        
        $userName = MicrosoftActions::getUserName($accessToken);
        $userPhoto = MicrosoftActions::getProfilePhoto($accessToken) ?? $_ENV['DEFAULT_PROFILE_PHOTO'];

    }else if($userId == NULL && $accessToken == NULL){
        header('Location: index.php?sesion=no-started');
        exit();
    }else if($admin == NULL){
        include('php/views/alerts.php');
        exit();
    }else if($userId != NULL && $accessToken == NULL && $admin == 'Local'){
        $userDataInstance = new userData($connection);
        $GetCurrentUserData = $userDataInstance->GetCurrentUserData($userId);

        if (!$GetCurrentUserData['success']) {
            echo 'Error al obtener los datos del usuario';
            $userName = 'Usuario';
            $userPhoto = $_ENV['NO_PHOTO'];
        }else{            
            $userName = $GetCurrentUserData['userName'];
            $userEmail = $GetCurrentUserData['email'];
            $userPhone = $GetCurrentUserData['phone'];
            $userPhoto = $_ENV['DEFAULT_PROFILE_PHOTO'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/all.min.css">
    <link rel="stylesheet" href="assets/css/groups.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Grupos</title>
</head>
<body>

    <?php include_once 'backend/views/mainMenu.php'; ?>
      
    <section class="home" id="home">           
        <div class="text">Grupos</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lista completa</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table" id="groupsTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Carrera</th>
                                        <th class="text-center">Clave</th>
                                        <th class="text-center">Nombre</th>
                                        <th class="text-center">Fecha inicio</th>
                                        <th class="text-center">Fecha termino</th>
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
    </section>

</body>
</html>

<!-- Modal EDIT -->
<div class="modal fade modal-lg" id="GroupsEditModal" data-bs-backdrop="static" tabindex="-1" aria-labelledby="GroupsEditModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="GroupsEditModalLabel">Editar Grupo</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="updateGroup">
            <div class="row g-2">
                <div class="col-md hidden">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="idGroupDB" name="idGroupDB" readonly>
                    <label for="idGroupDB">ID</label>
                    </div>
                    <input type="text" class="form-control hidden" id="idCarreerHidden" name="idCarreerHidden" readonly>
                </div>
                <div class="col-md">
                    <div class="form-floating">                   
                    <select class="form-select" id="carreerNameGroupEdit" name="carreerNameGroupEdit"  aria-label="Floating label select example">
                        <option selected value="0">Carrera</option>   
                    </select>
                    <label for="floatingSelect">Selecciona</label>
                    </div>
                    <label id="carreerNameGroupEdit-error" class="error text-bg-danger" for="carreerNameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="keyGroupEdit" name="keyGroupEdit" value="">
                    <label for="keyGroupEdit">Clave del grupo</label>
                    </div>
                    <label id="keyGroupEdit-error" class="error text-bg-danger" for="keyGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="nameGroupEdit" name="nameGroupEdit" value="">
                    <label for="nameGroupEdit">Nombre</label>
                    </div>
                    <label id="nameGroupEdit-error" class="error text-bg-danger" for="nameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="date" class="form-control" id="startDateEdit" name="startDateEdit" value="">
                    <label for="startDateEdit">Fecha de Inicio</label>
                    </div>
                    <label id="startDateEdit-error" class="error text-bg-danger" for="startDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
                <div class="col-md">
                    <div class="form-floating">
                    <input type="date" class="form-control" id="endDateEdit" name="endDateEdit" value="">
                    <label for="endDateEdit">Fecha de Termino</label>
                    </div>
                    <label id="endDateEdit-error" class="error text-bg-danger" for="endDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
            </div>
            <div class="row g-2 py-1">
                <div class="col-md">
                    <div class="form-floating">
                    <input type="text" class="form-control" id="descriptionGroupEdit" name="descriptionGroupEdit" value="">
                    <label for="descriptionGroupEdit">Descripción</label>
                    </div>
                    <label id="descriptionGroupEdit-error" class="error text-bg-danger" for="descriptionGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
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
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

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

<!-- Custom JS -->
<script type="module" src="js/groups/index.js"></script>
<script src="js/utils/validate.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>