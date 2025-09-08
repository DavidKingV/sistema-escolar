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
    echo '<div class="alert alert-warning" role="alert">
    La sesión a cadudado, por favor inicia sesión nuevamente
    </div>';
    exit();
}

$studentId = $_POST['studentId'] ?? NULL;
$studentName = $_POST['studentName'] ?? NULL;
$studentStatus = $_POST['studentStatus'] ?? NULL;

?>

<form action="" id="addEvent">
    <div class="form-group">
        <div class="mb-3">
            <label for="studentName">Nombre del alumno: </label>
            <input class="form-control" id="studentName" name="studentName" type="text" placeholder="" value="<?php echo $studentName ?>" readonly>    
        </div>
        <div class="mb-3" hidden>
            <label for="studentId">ID:</label>
            <input class="form-control" id="studentId" name="studentId" type="text" placeholder="" value="<?php echo $studentId ?>" readonly>      
        </div>

        <div class="mb-3">
            <label for="studentStatus">Estatus:</label>
            <select name="studentStatus" id="studentStatus" class="form-control">
                <option value="0" >Selección</option>
                <option value="1" <?php echo $studentStatus === '1' ? 'selected' : '' ?>>Activo</option>
                <option value="2" <?php echo $studentStatus === '2' ? 'selected' : '' ?>>Baja Temporal</option>
                <option value="3" <?php echo $studentStatus === '3' ? 'selected' : '' ?>>Inactivo</option>
                <option value="4" <?php echo $studentStatus === '4' ? 'selected' : '' ?>>Egresado</option>
            </select>
        </div>       

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" id="updateStudentStatus" class="btn btn-success">Actualizar</button>
        </div>
    </div>
</form>

<script type="module">    
    import { errorAlert, successAlert, infoAlert, loadingSpinner, loadingAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/utils/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/fetchCall.js';

    let api = 'public/api.php';

    $('#addEvent').submit(function(e) {
        e.preventDefault();
        
        let statusData = $(this).serialize();

        loadingAlert();

        sendFetch(api, 'POST', { action: 'updateStatus', statusData })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                    }
                    return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                })
                .then(data => {
                    if (data.success) {
                        if(data.error != null)infoAlert(data.error);
                        successAlert(data.message);
                        $('#statusModal').modal('hide');
                        $('#studentTable').DataTable().ajax.reload();
                    } else {
                        errorAlert(data.message);
                    }
                });

        
    });

</script>   