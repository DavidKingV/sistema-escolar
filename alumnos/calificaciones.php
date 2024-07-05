<?php
require_once(__DIR__.'/../php/vendor/autoload.php');
require_once __DIR__ . '/../php/students/verify.php';

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

session_start();

$VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);

if (!$VerifySession['success']) {
    header('Location: ../index.html?sesion=expired');
    exit();
}else{
    $userId = $VerifySession['userId'];

    $dbConnection = new DBConnection();
    $connection = $dbConnection->getConnection();

    $userDataInstance = new userData($connection);
    $GetCurrentUserData = $userDataInstance->GetCurrentUserData($userId);


    if (!$GetCurrentUserData['success']) {
        echo 'Error al obtener los datos del usuario';
    }else{
        $userName = $GetCurrentUserData['userName'];
        $userEmail = $GetCurrentUserData['email'];
        $userPhone = $GetCurrentUserData['phone'];
    }

    if (!isset($_GET['id']) && !isset($_GET['jtw'])) {
        header('Location: ../alumnos.php');
        exit();
    }else{
        $studentId = $_GET['id'];
        $studentSecretKey = $_GET['jtw'];

        $StudentsIdControl = new AdvancedStudentsControl();
        $GetStudentId = $StudentsIdControl->VerifyIdStudentId($studentId, $studentSecretKey);

        if (!$GetStudentId['success']) {
            echo 'Error al verificar el id del estudiante';
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
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Altas</title>
</head>
<body>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="../assets/img/escudo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-mid">
            ESMEFIS Centro Universitario
          </a>
        </div>
    </nav>

    <nav class="sidebar" id="nav">

    <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px; min-height: calc(100vh);">
            <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item py-1">
                <a href="../dashboard.php" class="btn btn-light d-flex align-items-center justify-content-start"><i class="bi bi-house-fill px-3"></i>Inicio</a>
            </li>
            <li class="py-1">                 
                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-person-badge-fill px-3"></i>Alumnos
                </button>                
                <div class="collapse" id="collapseStudents">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../alumnos.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
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
                            <a href="../profesores/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../profesores.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="../profesores/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
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
                            <a href="altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../grupos.php" class="list-group-item list-group-item-action">Lista</a>
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
                            <a href="../carreras/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../carreras.php" class="list-group-item list-group-item-action">Lista</a>
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
                            <a href="../materias/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../materias.php" class="list-group-item list-group-item-action">Lista</a>
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
                <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
                <strong><?php echo $userName ?></strong>
            </a>
            <ul class="dropdown-menu text-small shadow" aria-labelledby="dropdownUser2">
                <li><a class="dropdown-item" href="#">Cerrar Sesión</a></li>
            </ul>
            </div>
        </div>
    </nav>
      
    <section class="home" id="home">           
        <div class="text">Calificaciones del Alumno</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title">Agregar calificación</h4>
                    </div>                   
                    <div class="card-body">
                        <div class="row g-2">
                            <form id="addGradeStudent">
                            
                                <table class="table table-striped" id="addGradesTable">
                                    <thead>
                                        <tr>
                                        <th scope="col">Materia</th>
                                        <th scope="col">Calificación Continua</th>
                                        <th scope="col">Calificación de Examen</th>
                                        <th scope="col">Calificación Final</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                        <th scope="row">
                                            <div class="row g-2">  
                                                <div class="col-md">
                                                    <select class="form-select subjectName" name="subject" id="subject" style="">
                                                        <option value="0">Selecciona una materia</option>
                                                    </select>  
                                                </div>
                                                <div class="col-md">
                                                    <select class="form-select subjectChildName" name="subjectChild" id="subjectChild" style="">
                                                        <option value="0">Selecciona una submateria</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </th>
                                        <td><input type="text" name="gradeCont" id="gradeCont" class="form-control"></td>
                                        <td><input type="text" name="gradetest" id="gradetest" class="form-control"></td>
                                        <td><input type="text" name="gradefinal" id="gradefinal" class="form-control"></td>
                                        </tr>                                 
                                    </tbody>
                                </table>
                                
                        </div>
                                <div class="row g-2 py-3">
                                    <div class="col-md">
                                        <button type="submit" class="btn btn-success">Agregar</button>
                                    </div>                                
                                </div>

                            </form>

                    </div>
                </div>    
                
                <div class="card mb-4">                   
                    
                    <div class="card-header">
                        <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="studentsListGrades" data-bs-toggle="tab" data-bs-target="#studentGradesTable" type="button" role="tab" aria-controls="studentGradesTable" aria-selected="true">Calificaciones</button>
                        </li>
                        <li class="nav-item">
                        <button class="nav-link" id="studentDetails" data-bs-toggle="tab" data-bs-target="#studentGroupDetails" type="button" role="tab" aria-controls="studentGroupDetails" aria-selected="true">Grupo</button>
                        </li>                       
                        </ul>
                    </div>                    

                    <div class="card-body"> 
                        <div class="tab-content" id="myTabContent">

                            <div class="tab-pane fade show active" id="studentGradesTable" role="tabpanel" aria-labelledby="studentGradesTable" tabindex="0">
                                <h4 class="card-title py-3">Lista de calificaciones</h4>
                                <table class="table table-secondary table-striped" id="gradesStudentTable">
                                    <thead>
                                        <tr>             
                                            <th class="text-center">ID</th>                           
                                            <th class="text-center">Materia</th>
                                            <th class="text-center">C. Continua</th>
                                            <th class="text-center">C. de Examen</th>
                                            <th class="text-center">C. Final</th>
                                            <th class="text-center">Ult. Actualización</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                    </tbody>    
                                </table>
                            </div>

                            <div class="tab-pane fade show" id="studentGroupDetails" role="tabpanel" aria-labelledby="studentGroupDetails" tabindex="0">
                                <div id="alertDisplay">
                                    <h4 class="card-title py-3">Agregar alumno al grupo</h4>
                                    <form id="studentGroupDetailsForm">
                                        <div class="row g-2">                                
                                            <div class="col-md">                                            
                                                <select class="form-select" id="studentIdGroup" name="studentIdGroup">                                                 
                                                </select>     
                                                <div>
                                                    <p class="py-1">                                                                        
                                                    <label id="studentIdGroup-error" class="error text-bg-danger" for="studentIdGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
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
<script type="module" src="../js/students/index.js"></script>
<script src="../js/utils/validate.js"></script>