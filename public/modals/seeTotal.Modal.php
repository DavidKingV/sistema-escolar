<?php
require_once(__DIR__.'/../../backend/vendor/autoload.php');

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Vendor\Schoolarsystem\MicrosoftActions;
use Vendor\Schoolarsystem\loadEnv;

session_start();

loadEnv::cargar();
$VerifySession = auth::check();

$dbConnection = new DBConnection();
$connection = $dbConnection->getConnection();

if (!$VerifySession['success']) {
    echo '<div class="alert alert-warning" role="alert">
    La sesión a cadudado, por favor inicia sesión nuevamente
    </div>';
    exit();
}

$studentId = $_POST['id'] ?? NULL;
$totalHours = $_POST['total'] ?? NULL;

?>

<p class="placeholder-glow">
    <span class="placeholder col-12" id="placeholderStudent"><h3 id="studentNameH"></h3></span>
</p></div>
<div>
    <div class="text"><h5> Total de horas practicas: <?php echo $totalHours ?></h5></div>
    <hr class="border-top">
<div>
<div class="card-body">
    <table  id="studentHoursDataTable" class="table table-bordered table-hover table-responsive">
        <thead>
             <tr>
                <th>Fecha</th>
                <th>Horas del día</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>                            
        </tbody>
    </table>
</div>

<script type="module">
    import { errorAlert, successAlert, infoAlert, loadingSpinner, loadingAlert, selectAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';
    import { fullCalendar } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fullcalendar/index.js';
    import { initializeDataTable } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/dataTables.js';

    const callback = '<?php echo $_ENV['BASE_URL']; ?>/api.php';
    const studentId = '<?php echo $studentId; ?>';

    $(function() {

        initializeDataTable('#studentHoursDataTable', callback, {studentId: studentId, action: 'getStudentlHoursData' }, [
        { "data": "date", "className": "text-center" },
        { "data": "hours", "className": "text-center" },
        { "data": "status", "className": "text-center" },             
        ]);    

        getStudentName(studentId);

    });

    const getStudentName = async (studentId) => {
    try{
        sendFetch(callback, 'POST', { action: 'getStudentName', studentId: studentId })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                    }
                    return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                })
                .then(data => {
                    if (data.success) {
                        $("#studentNameH").text(data.studentName);
                        $("#placeholderStudent").attr("class", "studentName");
                    } else {
                        errorAlert(data.message);
                    }
                });     
    }catch(error){
        errorAlert(error);
    }
}


</script>