<?php
require_once(__DIR__.'/../../backend/vendor/autoload.php');

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
        header('Location: ../index.php?sesion=no-started');
        exit();
    }else if($admin == NULL){
        include __DIR__.'/../../backend/views/alerts.php';
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
    <link rel="stylesheet" href="../assets/css/groups.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Horarios</title>
</head>
<body>

    <?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
      
    <section class="home" id="home">           
        <div class="text">Horarios del grupo</div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Agregar</h6>
                    </div>
                    <div class="card-body">
                        <form id="formAddSchedule" class="row g-3">
                            <div class="col-md-4" hidden>
                                <label for="groupId" class="form-label">Grupo: </label>
                                <input type="text" class="form-control" id="groupId" name="groupId" value="<?php echo $_GET['id'] ?? NULL; ?>" required>
                            </div>

                            <div class="col-md-6">
                                <label for="title" class="form-label">Titulo: </label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            <div class="col-md-6">
                                <label for="date" class="form-label">Fecha: </label>
                                <input type="date" class="form-control" id="date" name="date" required>
                            </div>
                            <div class="col-md-6">
                                <label for="inputStart" class="form-label">Hora de inicio: </label>
                                <input type="time" class="form-control" id="inputStart" name="inputStart" required>
                            </div>
                            <div class="col-md-6">
                                <label for="inputEnd" class="form-label">Hora de fin: </label>
                                <input type="time" class="form-control" id="inputEnd" name="inputEnd" required>
                            </div>

                           
                            <div class="col-md-12">
                                <label for="description" class="form-label">Descripción: </label>
                                <textarea class="form-control" id="description" name="description" required rows="5"></textarea>
                            </div>
                            
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">Agregar</button>
                            </div>
                        </form>
                    </div>
                </div>                        

            </div>

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Lista de eventos para el grupo</h6>
                    </div>
                    <div class="card-body">
                        <table id="schedulesTable" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Titulo</th>
                                    <th>Fecha</th>
                                    <th>Hora de inicio</th>
                                    <th>Hora de fin</th>
                                    <th>Descripción</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
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
<script type="module" src="../public/js/groupsSchedules.js"></script>
<script src="js/utils/validate.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>