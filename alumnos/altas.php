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
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    <!--<link rel="stylesheet" href="../assets/css/alumnos.css">-->
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Altas</title>
</head>
<body>

    <?php include('../backend/views/mainMenu.php'); ?>
      
    <section class="home" id="home">           
        <div class="text">Alta de Alumnos</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary">Datos</h6>
                    </div>
                    <div class="card-body">
                        <form id="addStudents">
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="studentName" class="form-label">Nombre Completo</label>
                                    <label id="studentName-error" class="error text-bg-danger" for="studentName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="studentName" name="studentName">
                                    <div id="userList" class="list-group"></div>
                                </div>
                                <div class="col-md py-3">
                                    <label for="controlNumber" class="form-label">No. Control Interno</label>
                                    <label id="controlNumber-error" class="error text-bg-danger" for="controlNumber" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="controlNumber" name="controlNumber" placeholder="123456">                                    
                                </div>  
                                <div class="col-md py-3">
                                    <label for="controlSepNumber" class="form-label">No. Control SEP</label>
                                    <label id="controlSepNumber-error" class="error text-bg-danger" for="controlSepNumber" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="controlSepNumber" name="controlSepNumber" placeholder="123456">                                    
                                </div>                               
                            </div>
                            <div class="row g-2" id="microsoftDiv" style="display: none;">
                                <div class="col-md py-3">
                                    <div class="alert alert-success" role="alert">
                                        <h5 class="alert-heading">Usuario Microsoft Encontrado</h5>
                                        <input type="text" readonly class="form-control-plaintext" id="microsoftId" name="microsoftId" >                                   
                                        <input type="text" readonly class="form-control-plaintext" id="microsoftEmail" name="microsoftEmail" >
                                    </div>
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="studentGender" class="form-label">Genero</label>
                                    <label id="studentGender-error" class="error text-bg-danger" for="studentGender" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="studentGender" name="studentGender">
                                        <option selected value="0">Selecciona</option>
                                        <option value="Masculino">Masculino</option>
                                        <option value="Femenino">Femenino</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>
                                <div class="col-md py-3">
                                    <label for="studentBirthday" class="form-label">Fecha de nacimiento</label>
                                    <label id="studentBirthday-error" class="error text-bg-danger" for="studentBirthday" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="date" class="form-control" id="studentBirthday" name="studentBirthday">
                                </div>
                                <div class="col-md py-3">
                                    <label for="studentState" class="form-label">Estado Civil</label>
                                    <label id="studentState-error" class="error text-bg-danger" for="studentState" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <select class="form-select" id="studentState" name="studentState">
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
                                    <label for="studentNation" class="form-label">Nacionalidad</label>
                                    <label id="studentNation-error" class="error text-bg-danger" for="studentNation" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="studentNation" name="studentNation">
                                </div>
                                <div class="col-md py-3">
                                    <label for="studentCurp" class="form-label">CURP</label>
                                    <label id="studentCurp-error" class="error text-bg-danger" for="studentCurp" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="studentCurp" name="studentCurp">
                                </div>
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="studentPhone" class="form-label">Teléfono</label>
                                    <label id="studentPhone-error" class="error text-bg-danger" for="studentPhone" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <div class="input-group">
                                        <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">País</button>
                                        <ul class="dropdown-menu">
                                            <li><button class="dropdown-item" type="button" data-id="52">México</button></li>
                                            <li><button class="dropdown-item" type="button" data-id="54">Argentina</button></li>
                                            <li><button class="dropdown-item" type="button" data-id="57">Colombia</button></li>
                                            <li><button class="dropdown-item" type="button" data-id="01">Estados Unidos</button></li>
                                            <li><button class="dropdown-item" type="button" data-id="01">Canadá</button></li>
                                        </ul>
                                        <input type="text" class="form-control" id="studentPhone" name="studentPhone">
                                    </div>
                                </div>
                                <div class="col-md py-3">
                                    <label for="studentEmail" class="form-label">Email</label>
                                    <label id="studentEmail-error" class="error text-bg-danger" for="studentEmail" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="studentEmail" name="studentEmail">
                                </div>
                            </div>

                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                
                                    <div class="form-check form-switch py-4">
                                        <input class="form-check-input" type="checkbox" role="switch" id="noExtraData">
                                        <label class="form-check-label" for="noExtraData">No se cuenta con todos los datos</label>
                                    </div>
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
<script type="module" src="../js/students/index.js"></script>
<script src="../js/utils/validate.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>