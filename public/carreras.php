<?php
require_once(__DIR__.'/../backend/vendor/autoload.php');

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
        include __DIR__.'/../backend/views/alerts.php';
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
    <link rel="stylesheet" href="assets/css/carreers.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Alumnos</title>
</head>
<body>

    <?php include_once __DIR__.'/../backend/views/mainMenu.php'; ?>
      
    <section class="home" id="home">           
        <div class="text">Carreras</div>
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
<script type="module" src="js/carreers/index.js"></script>
<script type="module" src="js/carreers/carreers.js"></script>

<script type="module" src="js/careers.js"></script>

<script src="js/utils/validate.js"></script>
<script type="module" src="js/utils/sessions.js"></script>