<?php
require_once(__DIR__.'/../php/vendor/autoload.php');

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
    header('Location: ../index.php?sesion=expired');
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
        header('Location: ../index.php?sesion=no-started');
        exit();
    }else if($admin == NULL){
        include('../php/views/alerts.php');
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
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <!--<link rel="stylesheet" href="../assets/css/alumnos.css">-->
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Altas</title>
</head>
<body>

    <?php include('../backend/views/mainMenu.php'); ?>
      
    <section class="home" id="home">           
        <div class="text">Detalles del Grupo</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">                   
                    <div class="card-body">
                        <h4 class="card-title py-3">Información completa</h4>
                        
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <h5>Carrera:</h5>
                                <p id="groupCarreerNameDetails"></p>
                            </div>
                            <div class="col-md py-3">
                                <h5>Clave del grupo:</h5>
                                <p id="groupKeyDetails"></p>
                            </div>
                            <div class="col-md py-3">
                                <h5>Nombre del grupo:</h5>
                                <p id="groupNameDetails"></p>
                            </div>
                            <div class="col-md py-3">
                                <h5>Fecha de Inicio:</h5>
                                <p id="groupInitialDateDetails"></p>
                            </div>
                            <div class="col-md py-3">
                                <h5>Fecha de Termino:</h5>
                                <p id="groupFinalDateDetails"></p>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md py-3">
                                <h5>Descripción del grupo:</h5>
                                <p id="groupDescriptionDetails"></p>
                            </div>                            
                        </div>

                    </div>
                </div>    
                
                <div class="card mb-4">                   
                    
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="studentsList" data-bs-toggle="tab" data-bs-target="#studentListTab" type="button" role="tab" aria-controls="studentListTab" aria-selected="true">Alumnos</button>
                        </li>
                        <li class="nav-item">
                        <button class="nav-link" id="studentsList" data-bs-toggle="tab" data-bs-target="#addStudentGroup" type="button" role="tab" aria-controls="addStudentGroup" aria-selected="true">Agregar</button>
                        </li>                       
                        </ul>
                    </div>                    

                    <div class="card-body"> 
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="studentListTab" role="tabpanel" aria-labelledby="studentListTab" tabindex="0">
                                <h4 class="card-title py-3">Lista de alumnos</h4>
                                <table class="table" id="groupStudentsTable">
                                    <thead>
                                        <tr>             
                                            <th class="text-center">ID</th>                           
                                            <th class="text-center">Alumno</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>    
                                </table>
                            </div>

                            <div class="tab-pane fade show" id="addStudentGroup" role="tabpanel" aria-labelledby="addStudentGroup" tabindex="0">
                                <h4 class="card-title py-3">Agregar alumno al grupo</h4>
                                <form id="addStudentGroupForm">
                                    <div class="row g-2">                                
                                        <div class="col-md">                                            
                                            <select class="form-select" id="studentIdGroup" name="studentIdGroup[]" multiple="multiple">                                                 
                                            </select>     
                                            <div>
                                                <p class="py-1">                                                                        
                                                <label id="studentNameGroup-error" class="error text-bg-danger" for="studentNameGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                                                </p>
                                            </div>                                        
                                        </div>                                                                                
                                    </div>
                                    <div class="col-md py-1">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Agregar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>                        


            </div>

        </div>
    </section>

</body>
</html>


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
<script type="module" src="../js/groups/index.js"></script>
<script type="module" src="../public/js/groupsDetails.js"></script>
<script src="../js/utils/validate.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>