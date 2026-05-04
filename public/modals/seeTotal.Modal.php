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
    import { errorAlert, successAlert, infoAlert, loadingSpinner, loadingAlert, confirmAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';
    import { fullCalendar } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fullcalendar/index.js';
    import { initializeDataTable } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/dataTables.js';

    const callback = '<?php echo $_ENV['BASE_URL']; ?>/api.php';
    const apiStudents = '<?php echo $_ENV['BASE_URL_BACKEND']; ?>/students/routes.php';
    const studentId = '<?php echo $studentId; ?>';

    $(async function() {

        await initializeDataTable('#studentHoursDataTable', callback, {studentId: studentId, action: 'getStudentlHoursData' }, [
        { "data": "date", "className": "text-center" },
        { "data": "hours", "className": "text-center" },
        { "data": "status", "className": "text-center" }, 
        { 
            "data": "actions",
            "render": function(data, type, row) {
                if (!data) return "";
                return `<button data-id="`+row.id+`" class="btn btn-danger btn-circle deleteHour"><i class="bi bi-trash-fill"></i></button>`;
            },
            "className": "text-center"
        },            
        ]);    

        await getStudentName(studentId);

    });

    const getStudentName = async (studentId) => {
        try{
            sendFetch(apiStudents, 'POST', { action: 'getStudentName', studentId: studentId })                
                .then(data => {
                    if (data.success) {
                        $("#seeTotalModalLabel").text(data.studentName);
                        $("#placeholderStudent").attr("class", "studentName");
                    } else {
                        errorAlert(data.message);
                    }
                })
            .catch(error => {
                console.error('Error:', error);
                return { success: false, message: error}; // Asegúrate de que se devuelve en caso de error
            });  
        }catch(error){
            errorAlert(error);
        }
    }

    $("#studentHoursDataTable").on("click", ".deleteHour", function(){        
        let hourId = $(this).data("id");

        confirmAlert('¿Estás seguro de eliminar esta hora?', 'Sí, eliminar', 'Cancelar')
        .then((result) => {
            if (result.isConfirmed) {
                sendFetch(callback, 'POST', { action: 'deleteHour', hourId: hourId })                
                .then(data => {
                    if (data.success) {
                        successAlert(data.message);
                        $('#studentHoursDataTable').DataTable().ajax.reload();
                    } else {
                        errorAlert(data.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    return { success: false, message: error}; // Asegúrate de que se devuelve en caso de error
                });  
            }
        });
    });


</script>