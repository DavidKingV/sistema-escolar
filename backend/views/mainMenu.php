<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Vendor\Schoolarsystem\MicrosoftActions;
use Vendor\Schoolarsystem\loadEnv;

loadEnv::cargar();
$VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);

$dbConnection = new DBConnection();
$connection = $dbConnection->getConnection();

if ($VerifySession['success']) {
    $userId = $VerifySession['userId'] ?? NULL;
    $accessToken = $VerifySession['accessToken']?? NULL;
    $admin = $VerifySession['admin'];

    $userName='';
    $userPhoto = '';

    if($userId !== NULL && $accessToken != NULL && $admin == true){
        
        $userName = MicrosoftActions::getUserName($accessToken);
        $userPhoto = MicrosoftActions::getProfilePhoto($accessToken) ?? $_ENV['DEFAULT_PROFILE_PHOTO'];

    }else if($userId == NULL && $accessToken == NULL){
        header('Location: index.php?sesion=no-started');
        exit();
    }else if($admin == NULL){
        include __DIR__.'/alerts.php';
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

<nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="<?php echo $_ENV['BASE_URL']; ?>/assets/img/escudo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-mid">
            ESMEFIS Centro Universitario
          </a>
        </div>
    </nav>

    <nav class="sidebar" id="nav">

        <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px; min-height: calc(100vh);">
            <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item py-1">
                <a href="<?php echo $_ENV['BASE_URL']; ?>/dashboard.php" class="btn btn-light d-flex align-items-center justify-content-start"><i class="bi bi-house-fill px-3"></i>Inicio</a>
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-currency-dollar px-3"></i>Pagos
                </button>                
                <div class="collapse" id="collapsePayments">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/public/pagos/nuevo-pago.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/public/pagos.php" class="list-group-item list-group-item-action">Lista</a>
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-person-badge-fill px-3"></i>Alumnos
                </button>                
                <div class="collapse" id="collapseStudents">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClinicalHours" aria-expanded="false" aria-controls="collapseClinicalHours">
                <i class="bi bi-file-earmark-medical-fill px-3"></i>Horas practicas
                </button>                
                <div class="collapse" id="collapseClinicalHours">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/public/practicas/calendario.php" class="list-group-item list-group-item-action">Calendario</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/public/practicas/historial.php" class="list-group-item list-group-item-action">Historial</a>
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeachers" aria-expanded="false" aria-controls="collapseTeachers">
                <i class="bi bi-person-workspace px-3"></i>Profesores
                </button>                
                <div class="collapse" id="collapseTeachers">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
                        </div>                          
                    </div>
                </div> 
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGroups" aria-expanded="false" aria-controls="collapseGroups">
                <i class="bi bi-person-badge-fill px-3"></i>Grupos
                </button>                
                <div class="collapse" id="collapseGroups">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/grupos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/grupos.php" class="list-group-item list-group-item-action">Lista</a>
                        </div>                          
                    </div>
                </div>  
            </li>
            <li class="py-1">                    
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCarreers" aria-expanded="false" aria-controls="collapseCarreers">
                <i class="bi bi-mortarboard-fill px-3"></i>Carreras
                </button>                
                <div class="collapse" id="collapseCarreers">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/carreras/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/carreras.php" class="list-group-item list-group-item-action">Lista</a>
                        </div>                          
                    </div>
                </div>  
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubjects" aria-expanded="false" aria-controls="collapseSubjects">
                <i class="bi bi-book-half px-3"></i>Materias
                </button>                
                <div class="collapse" id="collapseSubjects">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/materias/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="<?php echo $_ENV['BASE_URL']; ?>/materias.php" class="list-group-item list-group-item-action">Lista</a>
                        </div>                          
                    </div>
                </div>  
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseUsers" aria-expanded="false" aria-controls="collapseUsers">
                <i class="bi bi-person-lines-fill px-3"></i>Usuarios
                </button>                
                <div class="collapse" id="collapseUsers">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="#" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="#" class="list-group-item list-group-item-action">Lista</a>
                        </div>                          
                    </div>
                </div>  

            </li>
            </ul>
            <hr>
            <div class="dropdown">
            <a href="#" class="d-flex align-items-center link-dark text-decoration-none dropdown-toggle" id="dropdownUser2" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="<?php echo $userPhoto ?>" alt="" width="32" height="32" class="rounded-circle me-2">
                <strong><?php echo $userName ?></strong>
            </a>
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
                <li><a class="dropdown-item" id="endSession" href="#">Cerrar Sesión</a></li>
            </ul>
            </div>
        </div>
    </nav>