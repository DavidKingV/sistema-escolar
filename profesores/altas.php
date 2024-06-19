<?php
require_once(__DIR__.'/../php/vendor/autoload.php');

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;

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
    <title>Document</title>
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
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-person-badge-fill px-3"></i>Alumnos
                </button>                
                <div class="collapse" id="collapseStudents">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="../alumnos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../alumnos.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="../alumnos/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">
                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTeachers" aria-expanded="false" aria-controls="collapseTeachers">
                <i class="bi bi-person-workspace px-3"></i>Profesores
                </button>                
                <div class="collapse" id="collapseTeachers">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="../profesores.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
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
                            <a href="../grupos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
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
        <div class="text">Alta de Profesores</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary">Datos</h6>
                    </div>
                    <div class="card-body">
                        <form id="addTeachers">
                            <div class="row g-2">                                
                                <div class="col-md py-3">
                                    <label for="teacherName" class="form-label">Nombre Completo</label>
                                    <label id="teacherName-error" class="error text-bg-danger" for="teacherName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="teacherName" name="teacherName">
                                </div>                                
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="teacherGender" class="form-label">Genero</label>
                                    <label id="teacherGender-error" class="error text-bg-danger" for="teacherGender" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="teacherGender" name="teacherGender">
                                        <option selected value="0">Selecciona</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md py-3">
                                    <label for="teacherBirthday" class="form-label">Fecha de nacimiento</label>
                                    <label id="teacherBirthday-error" class="error text-bg-danger" for="teacherBirthday" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="date" class="form-control" id="teacherBirthday" name="teacherBirthday">
                                </div>
                                <div class="col-md py-3">
                                    <label for="teacherState" class="form-label">Estado Civil</label>
                                    <label id="teacherState-error" class="error text-bg-danger" for="teacherState" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="teacherState" name="teacherState">
                                        <option selected value="0">Selecciona</option>
                                        <option value="Solter@">Solter@</option>
                                        <option value="Casad@">Casad@</option>
                                        <option value="Divorsiad@">Divorsiad@</option>
                                        <option value="Unión Libre">Unión Libre</option>
                                        <option value="Viud@">Viud@</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                            </div>                            
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="teacherPhone" class="form-label">Teléfono</label>
                                    <label id="teacherPhone-error" class="error text-bg-danger" for="teacherPhone" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="teacherPhone" name="teacherPhone">
                                </div>
                                <div class="col-md py-3">
                                    <label for="teacherEmail" class="form-label">Email</label>
                                    <label id="teacherEmail-error" class="error text-bg-danger" for="teacherEmail" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="mail" class="form-control" id="teacherEmail" name="teacherEmail">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </form>
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


<!-- Custom JS -->
<script type="module" src="../js/teachers/index.js"></script>
<script src="../js/utils/validate.js"></script>