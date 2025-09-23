<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Vendor\Schoolarsystem\MicrosoftActions;
use Vendor\Schoolarsystem\loadEnv;

loadEnv::cargar();
$VerifySession = auth::check();

if (!$VerifySession['success']) {
    header('Location: '.$_ENV['BASE_URL'].'/index.php?sesion=no-started');
    exit();
}

$dbConnection = new DBConnection();
$connection   = $dbConnection->getConnection();

$userId      = $VerifySession['userId']      ?? null;
$accessToken = $VerifySession['accessToken'] ?? null;
$admin       = $VerifySession['admin']       ?? null;

// Valores por defecto
$userName  = 'Usuario';
$userPhoto = $_ENV['DEFAULT_PROFILE_PHOTO'];

// 🔹 Flujo según tipo de sesión
if ($admin === true && $userId && $accessToken) {
    // Caso: Microsoft login
    $userName  = MicrosoftActions::getUserName($accessToken);
    $userPhoto = MicrosoftActions::getProfilePhoto($accessToken) ?? $_ENV['DEFAULT_PROFILE_PHOTO'];

} elseif ($admin === 'Local' && $userId && !$accessToken) {
    // Caso: Usuario local
    $userDataInstance   = new userData($connection);
    $GetCurrentUserData = $userDataInstance->GetCurrentUserData($userId);

    if ($GetCurrentUserData['success']) {
        $userName  = $GetCurrentUserData['userName'];
        $userEmail = $GetCurrentUserData['email'];
        $userPhone = $GetCurrentUserData['phone'];
        $userPhoto = $_ENV['DEFAULT_PROFILE_PHOTO'];
    } else {
        $userPhoto = $_ENV['NO_PHOTO'];
        echo 'Error al obtener los datos del usuario';
    }

} elseif ($admin === null) {
    // Caso: No tiene permisos
    include __DIR__.'/alerts.php';
    exit();
}

?>

<div id="sidebar">
    <div class="sidebar-header d-flex justify-content-between align-items-center">
        <h3 class="mb-0">ESMEFIS</h3>
    </div>

    <ul class="nav flex-column">
        <li class="nav-item">
            <a href="<?php echo $_ENV['BASE_URL']; ?>/dashboard.php" class="sidebar-link">
                <i class="bi bi-house-fill px-3"></i>
                <span class="sidebar-link-text">Dashboard</span>
            </a>
        </li>

        <!-- Estudiantes -->
        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#studentsMenu" role="button" aria-expanded="false" aria-controls="studentsMenu">
                <i class="bi bi-person-badge-fill px-3"></i>
                <span class="sidebar-link-text">Alumnos</span>
            </a>
            <ul id="studentsMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-list"></i> Lista de Alumnos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos/altas.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Agregar Alumno
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/alumnos/usuarios.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-chart-bar"></i> Usuarios
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#paymentsMenu" role="button" aria-expanded="false" aria-controls="paymentsMenu">
                <i class="bi bi-currency-dollar px-3"></i>
                <span class="sidebar-link-text">Pagos</span>
            </a>
            <ul id="paymentsMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/pagos/nuevo-pago.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-list"></i> Agregar pagos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/pagos.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Lista de pagos
                    </a>
                </li>
            </ul>
        </li>

        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#practicalHoursMenu" role="button" aria-expanded="false" aria-controls="practicalHoursMenu">
                <i class="bi bi-file-earmark-medical-fill px-3"></i>
                <span class="sidebar-link-text">Horas Prácticas</span>
            </a>
            <ul id="practicalHoursMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/practicas/calendario.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-list"></i> Calendario
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/practicas/historial.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Historial
                    </a>
                </li>
            </ul>
        </li>

        <!-- Profesores -->
        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#teachersMenu" role="button" aria-expanded="false" aria-controls="teachersMenu">
                <i class="bi bi-person-workspace px-3"></i>
                <span class="sidebar-link-text">Profesores</span>
            </a>
            <ul id="teachersMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-list"></i> Lista de Profesores
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores/altas.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Agregar Profesor
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/profesores/usuarios.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Usuarios
                    </a>
                </li>
            </ul>
        </li>

        <!-- Grupos -->
        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#groupsMenu" role="button" aria-expanded="false" aria-controls="groupsMenu">
                <i class="bi bi-person-badge-fill px-3"></i>
                <span class="sidebar-link-text">Grupos</span>
            </a>
            <ul id="groupsMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/grupos.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-list"></i> Lista de Grupos
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/grupos/altas.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-plus-circle"></i> Agregar Grupo
                    </a>
                </li>
            </ul>
        </li>

        <!-- Carreras -->
        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#carrersMenu" role="button" aria-expanded="false" aria-controls="carrersMenu">
                <i class="bi bi-mortarboard-fill px-3"></i>
                <span class="sidebar-link-text">Carreras</span>
            </a>
            <ul id="carrersMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/carreras.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-edit"></i> Lista de Carreras
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/carreras/altas.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-file-alt"></i> Agregar Carrera
                    </a>
                </li>
            </ul>
        </li>

        <!-- Materias -->
        <li class="nav-item">
            <a class="sidebar-link dropdown-toggle" data-bs-toggle="collapse" href="#subjectsMenu" role="button" aria-expanded="false" aria-controls="subjectsMenu">
                <i class="bi bi-book-half px-3"></i>
                <span class="sidebar-link-text">Materias</span>
            </a>
            <ul id="subjectsMenu" class="submenu collapse">
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/materias.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-edit"></i> Lista de Materias
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo $_ENV['BASE_URL']; ?>/materias/altas.php" class="list-group-item list-group-item-action sidebar-link">
                        <i class="fas fa-file-alt"></i> Agregar Materia
                    </a>
                </li>
            </ul>
        </li>

        <!-- Otras opciones 
        <li class="nav-item">
            <a href="#" class="sidebar-link">
                <i class="fas fa-calendar-alt"></i>
                <span class="sidebar-link-text">Calendario</span>
            </a>
        </li>-->
        <li class="nav-item">
            <a href="#" class="sidebar-link" id="endSession">
                <i class="fas fa-cog"></i>
                <span class="sidebar-link-text">Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</div>

<!-- Header -->
<header id="header">
    <button class="toggle-btn" id="sidebarCollapse">
        <i class="bi bi-list"></i>
    </button>

    <div class="user-info">
        <div class=""><img src="<?php echo $_ENV['BASE_URL']; ?>/assets/img/escudo.png" alt="Logo" width="30" height="30" class="d-inline-block align-text-mid"></div>
        <span><?php echo $userName ?></span>
    </div>
</header>

<!--<nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="/assets/img/escudo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-mid">
            ESMEFIS Centro Universitario
          </a>
        </div>
    </nav>

    <nav class="sidebar" id="nav">

        <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px; min-height: calc(100vh);">
            <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item py-1">
                <a href="<>/dashboard.php" class="btn btn-light d-flex align-items-center justify-content-start"><i class=""></i>Inicio</a>
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments" aria-expanded="false" aria-controls="collapseStudents">
                <i class=""></i>Pagos
                </button>                
                <div class="collapse" id="collapsePayments">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="">Agregar</a>
                            <a href="">Lista</a>
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-person-badge-fill px-3"></i>Alumnos
                </button>                
                                                          
            </li>
            <li class="py-1">                 
                <button class="btn w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseClinicalHours" aria-expanded="false" aria-controls="collapseClinicalHours">
                <i class=""></i>Horas practicas
                </button>                
                                                                
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeachers" aria-expanded="false" aria-controls="collapseTeachers">
                <i class=""></i>Profesores
                </button>                
                
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseGroups" aria-expanded="false" aria-controls="collapseGroups">
                <i class=""></i>Grupos
                </button>                
                
            </li>
            <li class="py-1">                    
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCarreers" aria-expanded="false" aria-controls="collapseCarreers">
                <i class=""></i>Carreras
                </button>                
                
            </li>
            <li class="py-1">
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubjects" aria-expanded="false" aria-controls="collapseSubjects">
                <i class=""></i>Materias
                </button>                
                
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
                <img src=" alt="" width="32" height="32" class="rounded-circle me-2">
                <strong></strong>
            </a>
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
                <li><a class="dropdown-item" id="endSession" href="#">Cerrar Sesión</a></li>
            </ul>
            </div>
        </div>
    </nav>-->