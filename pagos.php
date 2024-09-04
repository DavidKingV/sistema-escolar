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
    <link rel="stylesheet" href="assets/css/jquery-ui.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Grupos</title>
</head>
<body>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="assets/img/escudo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-mid">
            ESMEFIS Centro Universitario
          </a>
        </div>
    </nav>

    <nav class="sidebar" id="nav">

        <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px; min-height: calc(100vh);">
            <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item py-1">
                <a href="dashboard.php" class="btn btn-light d-flex align-items-center justify-content-start"><i class="bi bi-house-fill px-3"></i>Inicio</a>
            </li>
            <li class="py-1">                 
                <button class="btn btn-primary w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePayments" aria-expanded="false" aria-controls="collapsePayments">
                <i class="bi bi-credit-card-fill px-3"></i>Pagos
                </button>                
                <div class="collapse" id="collapsePayments">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="pagos.php" class="list-group-item list-group-item-action">Agregar</a>
                            <!--<a href="alumnos.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="alumnos/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>-->
                        </div>                       
                    </div>
                </div>                                                    
            </li>
            <li class="py-1">                 
                <button class="btn btn-light w-100 d-flex align-items-center justify-content-start" type="button" data-bs-toggle="collapse" data-bs-target="#collapseStudents" aria-expanded="false" aria-controls="collapseStudents">
                <i class="bi bi-person-badge-fill px-3"></i>Alumnos
                </button>                
                <div class="collapse" id="collapseStudents">
                    <div class="card card-body">
                        <div class="list-group">                            
                            <a href="alumnos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="alumnos.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="alumnos/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
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
                            <a href="profesores/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="profesores.php" class="list-group-item list-group-item-action">Lista</a>
                            <a href="profesores/usuarios.php" class="list-group-item list-group-item-action">Usuarios</a>
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
                            <a href="grupos/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="grupos.php" class="list-group-item list-group-item-action">Lista</a>
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
                            <a href="carreras/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="carreras.php" class="list-group-item list-group-item-action">Lista</a>
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
                            <a href="materias/altas.php" class="list-group-item list-group-item-action">Agregar</a>
                            <a href="materias.php" class="list-group-item list-group-item-action">Lista</a>
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
      
    <section class="home" id="home">           
        <div class="text">Pagos</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Registrar nuevo cobro</h6>
                    </div>
                    <div class="card-body">
                        
                        <form id="paymentsForm">
                            <div class="row g-2 py-3">
                                <div class="col-md">
                                    <div class="col-md ">  
                                        <label for="floatingSelect">Selecciona</label>
                                        <label id="studentName-error" class="error text-bg-danger" for="studentName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                        <select class="form-select" id="studentName" name="studentName"  aria-label="Floating label select example">
                                            <option selected value="0">Nombre del estudiante</option> 
                                        </select>                                    
                                    </div>    
                                </div>    
                                
                                <div class="col-md">                                                
                                    <label for="floatingSelect">Fecha actual</label>
                                    <input class="form-check-input" type="checkbox" id="todayDate" name="todayDate" checked>
                                    
                                    <label id="todayDate-error" class="error text-bg-danger" for="todayDate" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <div class="input-group mb-3">                                                                    
                                    <input type="text" class="form-control" id="paymentDate" name="paymentDate">   
                                    </div>                      
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md">            
                                    <label for="floatingSelect">Selecciona</label>
                                    <label id="paymentConcept-error" class="error text-bg-danger" for="paymentConcept" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="paymentConcept" name="paymentConcept"  aria-label="Floating label select example">
                                        <option selected value="0">Concepto</option>   
                                        <option value="Inscripción">Inscripción</option>
                                        <option value="Mensualidad">Mensualidad</option>
                                    </select>                                   
                                </div>                                                            
                                <div class="col-md">
                                    <div class="mb-3">
                                        <label for="">Precio</label>
                                        <label id="paymentPrice-error" class="error text-bg-danger" for="paymentPrice" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>                                            
                                            <input type="text" class="form-control" id="paymentPrice" name="paymentPrice">
                                        </div>
                                    </div>                                    
                                </div> 
                                <div class="col-md">                                                
                                    <label for="floatingSelect">Recargo</label>
                                    <input class="form-check-input" type="checkbox" id="paymentExtraCkeck" name="paymentExtraCkeck" checked>
                                    
                                    <label id="paymentExtra-error" class="error text-bg-danger" for="paymentExtra" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <div class="input-group mb-3">   
                                    <span class="input-group-text">$</span>                                                                  
                                    <input type="text" class="form-control" id="paymentExtra" name="paymentExtra">   
                                    </div>                      
                                </div>
                                <div class="col-md">
                                    <div class="mb-3">
                                        <label for="">Total</label>
                                        <label id="paymentTotal-error" class="error text-bg-danger" for="paymentTotal" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>                                        
                                        <div class="input-group">
                                            <span class="input-group-text">$</span>                                                                                        
                                            <input type="text" class="form-control" id="paymentTotal" name="paymentTotal">
                                        </div>
                                    </div>                                        
                                </div> 
                            </div>

                            <div class="row g-2 py-2">
                                <div class="col-md">            
                                    <label for="floatingSelect">Selecciona</label>                                    
                                    <label id="paymentMethod-error" class="error text-bg-danger" for="paymentMethod" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="paymentMethod" name="paymentMethod"  aria-label="Floating label select example">
                                        <option selected value="0">Método de pago</option>   
                                        <option value="01">Efectivo</option>
                                        <option value="03">Transferencia bancaria</option>
                                        <option value="04">Tarjeta de crédito</option>
                                        <option value="28">Tarjeta de débito</option>
                                    </select>                                
                                </div>                                                                    
                                <div class="col-md">            
                                    <label for="floatingSelect">Selecciona</label>                                                                        
                                    <label id="paymentInvoice-error" class="error text-bg-danger" for="paymentInvoice" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="paymentInvoice" name="paymentInvoice"  aria-label="Floating label select example">
                                        <option selected value=" ">Tipo de comprobante</option>   
                                        <option value="0" data-id="0">Recibo simple</option>
                                        <option value="1" data-id="1">Factura</option>
                                    </select>                            
                                </div>     
                            </div>

                            <div class="row g-2 py-4" id="invoiceInfoDiv" hidden>
                                <div class="col-md">
                                    <div class="form-floating">                   
                                        <input type="text" class="form-control" id="fiscalId" name="fiscalId" readonly disabled>
                                        <label for="floatingInput">ID Fiscal</label>
                                    </div>                                    
                                </div>                                
                            </div>

                            <div class="row g-2 py-4">
                                <div class="col-md">
                                    <button type="submit" class="btn btn-primary">Registrar pago</button>
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
<script type="module" src="js/payments/index.js"></script>
<script src="js/utils/validate.js"></script>
<script type="module" src="js/utils/sessions.js"></script>
