<?php
require_once(__DIR__.'/../../php/vendor/autoload.php');

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

$makeOverId = $_POST['makeOverId'] ?? NULL;
$makeOverChildId = $_POST['makeOverChildId'] ?? NULL;

$studentId = $_POST['studentId'] ?? NULL;

?>

<div class="tab-content" id="">
    <div class="tab-pane fade show active py-4" id="" role="tabpanel">
        <!--<input type="text" id="carreerId" name="carreerId" value="" hidden>-->
        <div class="mb-3">
            <label for="subjectName" class="form-labels">Materia</label>
            <input type="text" class="form-control" id="subjectName" name="subjectName" value="" disabled>
        </div>

        <div class="mb-3">
            <label for="subjectChildName" class="form-labels">SubMateria</label>
            <input type="text" class="form-control" id="subjectChildName" name="subjectChildName" value="" disabled>
        </div>

        <div class="mb-3">
            <label for="continuosGrade" class="form-labels">Calificación continua</label>
            <input type="text" class="form-control" id="continuosGrade" name="continuosGrade" value="">
        </div>

        <div class="mb-3">
            <label for="examGrade" class="form-labels">Examen</label>
            <input type="text" class="form-control" id="examGrade" name="examGrade" value="">
        </div>

        <div class="mb-3">
            <label for="finalGrade" class="form-labels">Calificación final</label>
            <input type="text" class="form-control" id="finalGrade" name="finalGrade" value="">
        </div>
    </div>
</div>

<script type="module">
    import { validateForm, capitalizeFirstLetter, inputLowerCase } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/validate/index.js';
    import { loadingAlert, errorAlert, successAlert, infoAlert } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/fetchCall.js';

    const callback = '<?php echo $_ENV['BASE_URL']; ?>/public/api.php';

    let makeOverId = '<?php echo $makeOverId; ?>';
    let makeOverChildId = '<?php echo $makeOverChildId; ?>';

    $(function(){
        
        sendFetch(callback, 'POST', { action: 'getMakeOverDetails', makeOverId: makeOverId })
                .then(async response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                    }                    
                    return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                })
                .then(async data => {
                    if (data.success) {                                                                      
                        // Remover placeholders y mostrar inputs
                        $('#subjectName').val(data.grades[0].subject_nombre);
                        $('#subjectChildName').val(data.grades[0].subject_child_nombre);
                        $('#continuosGrade').val(data.grades[0].continuosGrade);
                        $('#examGrade').val(data.grades[0].examGrade);
                        $('#finalGrade').val(data.grades[0].finalGrade);
                    } else {
                        errorAlert(data.message);
                        $('#makeOverViewModal').modal('hide');
                    }
                });

    })

</script>