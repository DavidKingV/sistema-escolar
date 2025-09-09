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

$studentId = $_POST['id'] ?? NULL;

?>

<p class="placeholder-glow">
    <span class="placeholder col-12" id="placeholderStudent"><h3 id="studentNameH"></h3></span>
</p></div>

<form id="addHoursForm">
<div class="mb-3">
        <label for="date" class="form-label">Fecha a registrar</label>
        <input type="date" placeholder="" name="date" id="date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="start" class="form-label">Hora de entrada: </label>
        <div data-bs-toggle="time-picker"></div>
        <input type="text" placeholder="" name="start" id="start" class="form-control">
    </div>
    <div class="mb-3">
        <label for="end" class="form-label">Hora de salida: </label>
        <input type="text" placeholder="" name="end" id="end" class="form-control">
    </div>
    <div class="mb-3">
        <label for="totalHours" class="form-label">Horas totales</label>
        <div class="input-group">
            <button type="button" id="btn_menos" data-ajuste="-30" class="btn btn-danger">-</button>
            <input type="text" placeholder="" name="totalHours" id="totalHours" class="form-control text-center" readonly>
            <button type="button" id="btn_mas" data-ajuste="30" class="btn btn-success">+</button>
        </div>
    </div>
    <div class="d-grid">
        <button class="btn btn-success register" type="submit">Registrar</button>
    </div>
</form>


<script type="module">
    import { errorAlert, successAlert, infoAlert, loadingSpinner, loadingAlert, selectAlert, warningAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';
    import { fullCalendar } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fullcalendar/index.js';
    import { initializeDataTable } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/dataTables.js';

    const callback = '<?php echo $_ENV['BASE_URL']; ?>/api.php';
    const studentId = '<?php echo $studentId; ?>';

    let format = 'HH:mm';

    $(function() {
        getStudentName(studentId);
        configurarTimepickers();

        $('#addHoursForm').on('submit', function(e) {
            e.preventDefault();

            let data = $(this).serialize() + `&studentId=${studentId}`;

            addStudentHours(data);
        });

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

    const addStudentHours = async (data) =>{
        loadingAlert();
        try{
            sendFetch(callback, 'POST', { action: 'addStudentHours', data })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                        }
                        return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                    })
                    .then(data => {
                        if (data.success) {
                            $("#addHoursModal").modal('hide');
                            $("#studentHoursDataTable").DataTable().ajax.reload();
                            successAlert(data.message);
                        } else {
                            errorAlert(data.message);
                        }
                    });     
        }catch(error){
            errorAlert(error);
        }
    }

    function configurarTimepickers() {
        var opcionesComunes = {
            'minTime': '09:00am',
            'maxTime': '06:00pm',
            'timeFormat': 'H:i',
            'showDuration': false
        };

        $('#start, #end').timepicker(opcionesComunes);
    }

    // Manejador de eventos para 'hora_salida'
    $('#end').on('changeTime', function() {
        if ($('#start').val() === '') {
            infoAlert("Por favor ingrese la hora de entrada");
        }else{
            calcularHoras(format);
        }
    });

     // Manejador de eventos para 'start'
     $('#start').on('changeTime', function() {
        if ($('#end').val()) {
            calcularHoras(format);
        }
    });

    //funcion para que al borrarse la hora de salida se borre la hora total
    $('#end').on('keyup', function() {
        if($('#end').val() == ''){
            $('#totalHours').val('');
        }
    });

    function ajustarHora(ajuste) {
        var hora = $('#totalHours').val();
        var horaMoment = moment(hora, 'HH:mm');

        // Ajuste puede ser positivo para incrementar o negativo para decrementar
        var horaAjustada = horaMoment.add(ajuste, 'minutes').format('HH:mm');
        $('#totalHours').val(horaAjustada);
    }

    $('#btn_mas, #btn_menos').click(function() {
        // Obtiene el valor del data attribute 'ajuste' para saber si incrementar o decrementar
        var ajuste = parseInt($(this).data('ajuste'), 10);
        ajustarHora(ajuste);
    });

    function calcularHoras(format) {
        let start = $('#start').val();
        let end = $('#end').val();

        // Utilizando moment.js para parsear las horas
        var entrada = moment(start, format);
        var salida = moment(end, format);

        // Calculando la diferencia
        var duracion = moment.duration(salida.diff(entrada));

        // Convirtiendo la duración a horas y minutos
        var horas = Math.floor(duracion.asHours());
        var minutos = Math.floor(duracion.asMinutes()) - horas * 60;

        // Formateando la salida
        var horasTotales = `${horas < 10 ? '0' : ''}${horas}:${minutos < 10 ? '0' : ''}${minutos}`;

        // Validaciones y manejo de errores
        if (duracion.asMinutes() <= 0) {
            warningAlert("La hora de salida debe ser posterior a la hora de entrada");
        } else if (isNaN(duracion.asMinutes())) {
            warningAlert("Por favor, asegúrese de que las horas de entrada y salida sean válidas");
        } else {
            $('#totalHours').val(horasTotales);
        }
    }


</script>