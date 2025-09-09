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

$studentId = $_POST['studentId'] ?? NULL;
$subjectId = $_POST['subjectId'];
$subjectChildId = $_POST['subjectChildId'] ?? NULL;
$subjectChildName = $_POST['subjectChildName'] ?? NULL;
$subjectName = $_POST['subjectName'];
$gradeId = $_POST['gradeId'];


?>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active py-4" id="add_subject_tab" role="tabpanel">
        <form id="addMakeOverGrade">
            <!--<input type="text" id="carreerId" name="carreerId" value="" hidden>-->
            <div class="mb-3">
                <label for="subjectName" class="form-labels">Materia</label>
                <input type="text" class="form-control" id="subjectName" name="subjectName" value="<?php echo $subjectName; ?>" disabled>
            </div>

            <div class="mb-3">
                <label for="subjectName" class="form-labels">Submateria</label>
                <input type="text" class="form-control" id="subjectName" name="subjectName" value="<?php echo $subjectChildName; ?>" disabled>
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
            

            <div class="col-md">
                <button type="submit" class="btn btn-success">Agregar</button>
            </div>
        </form>
    </div>
    <div class="tab-pane fade" id="view_subjects_tab" role="tabpanel">
        <table class="table table-striped table-hover" id="subjectsListTable">
            <thead>
                <tr>
                    <th scope="col">Clave</th>
                    <th scope="col">Nombre</th>
                </tr>
            </thead>
            <tbody id="subjectsList">
                <tr>
                    <td colspan="3" class="text-center">No hay materias registradas</td>
                </tr>
            </tbody>
    </div>
</div>

<script type="module">
    import { validateForm, capitalizeFirstLetter, inputLowerCase } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/validate/index.js';
    import { loadingAlert, errorAlert, successAlert, infoAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';

    const callback = '<?php echo $_ENV['BASE_URL']; ?>/api.php';

    $(function(){
        validateForm("#addMakeOverGrade", {
            subjectName: {
                required: true,
                minlength: 3,
            },
            subjectChildName: {
                minlength: 3,
            },
            subjectGrade: {
                required: true,
                number: true,
                maxlength: 3,
            },
            subjectExam: {
                required: true,
                number: true,
                maxlength: 3,
            },
            subjectFinalGrade: {
                required: true,
                number: true,
                maxlength: 4,
            }
        },
        {
            subjectName: {
            required: "El nombre de la materia es requerido",
            minlength: "El nombre de la materia debe tener al menos 3 caracteres"
            },
            subjectChildName: {
                minlength: "El nombre de la submateria debe tener al menos 3 caracteres"
            },
            subjectGrade: {
                required: "La calificación continua es requerida",
                number: "La calificación continua debe ser un número",
                maxlength: "La calificación continua no puede ser mayor a 3 caracteres"
            },
            subjectExam: {
                required: "La calificación del examen es requerida",
                number: "La calificación del examen debe ser un número",
                maxlength: "La calificación del examen no puede ser mayor a 3 caracteres"
            },
            subjectFinalGrade: {
                required: "La calificación final es requerida",
                number: "La calificación final debe ser un número",
                maxlength: "La calificación final no puede ser mayor a 4 caracteres"
            }
        });

        $("#addMakeOverGrade").on("submit", function(e) {
        e.preventDefault();

        let gradesData = $(this).serialize() + `&studentId=<?php echo $studentId; ?>&subjectId=<?php echo $subjectId; ?>&subjectChildId=<?php echo $subjectChildId; ?>&gradeId=<?php echo $gradeId; ?>`;

        if($(this).valid()){
            loadingAlert();
            sendFetch(callback, 'POST', { action: 'addMakeOverGrade', gradesData })
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
                        $('#makeOverExamModal').modal('hide');
                    } else {
                        errorAlert(data.message);
                    }
                });
        }else{
            infoAlert('Por favor completa los campos correctamente');
        }
        });

        
        $("#continuosGrade, #examGrade").on("input", function(){
            AverageGrade();
        });
    })

    function AverageGrade(){
        let continuos_grade = parseFloat($("#continuosGrade").val());
        let exam_grade = parseFloat($("#examGrade").val());

        let average = (continuos_grade + exam_grade) / 2;

        $("#finalGrade").val(average);
    }
</script>