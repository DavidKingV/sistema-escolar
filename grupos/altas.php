<?php
require_once __DIR__ . '/../php/login/index.php';

session_start();

$LoginControl = new LoginControl($con);
$VerifySession = $LoginControl->VerifySession($_COOKIE['auth']);

if (!$VerifySession['success']) {
    header('Location: ../index.html?sesion=expired');
    exit();
}else{
    $userId = $VerifySession['userId'];

    $UsersControl = new UsersControl($con);
    $GetCurrentUserData = $UsersControl->GetCurrentUserData($userId);

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
                <a href="../dashboard.php" class="nav-link link-dark" aria-current="page">
                    <i class="bi bi-house-fill px-3"></i>
                Inicio
                </a>
            </li>
            <li class="py-1">
                <div class="dropdown">
                    <a  href="../alumnos.html" class="nav-link dropdown-toggle link-dark" data-bs-toggle="dropdown" >
                        <i class="bi bi-person-badge-fill px-3"></i>
                    Alumnos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="altas.php">Agregar</a></li>
                        <li><a class="dropdown-item" href="../alumnos.php">Lista</a></li>
                        <li><a class="dropdown-item" href="usuarios.php">Usuarios</a></li>
                    </ul>
                </div>
            </li>
            <li class="py-1">
                <div class="dropdown">
                    <a href="../profesores.php" class="nav-link dropdown-toggle link-dark" data-bs-toggle="dropdown">
                        <i class="bi bi-person-workspace px-3"></i>
                    Profesores
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="../profesores/altas.php">Agregar</a></li>
                        <li><a class="dropdown-item" href="../profesores.php">Lista</a></li>
                        <li><a class="dropdown-item" href="../profesores/usuarios.php">Usuarios</a></li>
                    </ul>
                </div>   
            </li>
            <li class="py-1">
                <div class="dropdown">
                    <a  href="grupos.php" class="nav-link dropdown-toggle active" data-bs-toggle="dropdown" >
                        <i class="bi bi-person-badge-fill px-3"></i>
                    Grupos
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="altas.php">Agregar</a></li>
                        <li><a class="dropdown-item" href="../grupos.php">Lista</a></li>
                    </ul>
                </div>
            </li>
            <li class="py-1">
                <a href="#" class="nav-link link-dark">
                    <i class="bi bi-mortarboard-fill px-3"></i>
                Carreras
                </a>
            </li>
            <li class="py-1">
                <a href="#" class="nav-link link-dark">
                    <i class="bi bi-book-fill px-3"></i>
                Materias
                </a>
            </li>
            <li class="py-1">
                <a href="#" class="nav-link link-dark">
                    <i class="bi bi-person-lines-fill px-3"></i>
                Usuarios
                </a>
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
        <div class="text">Alta de Grupos</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary">Datos</h6>
                    </div>
                    <div class="card-body">
                        <form id="addGroups">
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="carreerNameGroup" class="form-label">Selecciona</label>
                                    <label id="carreerNameGroup-error" class="error text-bg-danger" for="carreerNameGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="carreerNameGroup" name="carreerNameGroup">
                                        <option selected value="0">Carrera</option>   
                                    </select>                                    
                                </div>
                                <div class="col-md py-3">
                                    <label for="keyGroup" class="form-label">Clave del Grupo</label>
                                    <label id="keyGroup-error" class="error text-bg-danger" for="keyGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="keyGroup" name="keyGroup">
                                </div>
                                <div class="col-md py-3">
                                    <label for="nameGroup" class="form-label">Nombre del Grupo</label>
                                    <label id="nameGroup-error" class="error text-bg-danger" for="nameGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="nameGroup" name="nameGroup">
                                </div>
                            </div>
                            <div class="row g-2">                                
                                <div class="col-md py-3">
                                    <label for="startDate" class="form-label">Fecha de Inicio</label>
                                    <label id="startDate-error" class="error text-bg-danger" for="startDate" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="date" class="form-control" id="startDate" name="startDate">
                                </div>
                                <div class="col-md py-3">
                                    <label for="endDate" class="form-label">Fecha de Termino</label>
                                    <label id="endDate-error" class="error text-bg-danger" for="endDate" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="date" class="form-control" id="endDate" name="endDate">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="descriptionGroup" class="form-label">Descripción</label>
                                    <label id="descriptionGroup-error" class="error text-bg-danger" for="descriptionGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="descriptionGroup" name="descriptionGroup">
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

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/groups/index.js"></script>
<script src="../js/utils/validate.js"></script>