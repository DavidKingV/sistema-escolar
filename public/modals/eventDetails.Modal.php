<?php
require_once(__DIR__ . '/../../backend/vendor/autoload.php');

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

$eventId = $_POST['eventId'] ?? null;
$eventData = $_POST['eventData'] ?? null;
$dateTime = $_POST['dateTime'] ?? null;

?>

<form action="" id="confirmEventForm">
    <div class="form-group">
        <div class="mb-3">
            <label for="startDetails">Hora de ingreso: </label>
            <!-- Placeholder inicial -->
            <p class="placeholder-glow" id="startPlaceholder">
                <span class="placeholder col-12"></span>
            </p>
            <input class="form-control d-none" id="startDetails" name="startDetails" type="text" placeholder=""
                value="">
        </div>
        <div class="mb-3">
            <label for="endDetails">Hora de salida: </label>
            <!-- Placeholder inicial -->
            <p class="placeholder-glow" id="endPlaceholder">
                <span class="placeholder col-12"></span>
            </p>
            <input class="form-control d-none" id="endDetails" name="endDetails" type="text" placeholder="">
        </div>
        <div class="mb-3">
            <label for="totalHours" class="form-label">Horas totales: </label>
            <div class="input-group">
                <button type="button" id="btn_menos" data-fix="-30" class="btn btn-danger">-</button>
                <input type="text" placeholder="" name="totalHours" id="totalHours" class="form-control text-center"
                    readonly>
                <button type="button" id="btn_mas" data-fix="30" class="btn btn-success">+</button>
            </div>
        </div>
        <div class="mb-3" id="deleteRazonDiv" hidden>
            <label for="deleteRazon">Razón: </label>
            <select class="form-select" id="deleteRazon" name="deleteRazon" disabled>
                <option selected value="0">Elige</option>
                <option value="2">Falta de asistencia</option>
                <option value="3">Falta de notificada</option>
                <option value="4">Otro</option>
            </select>
        </div>

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" id="confirmHours" class="btn btn-success">Confirmar Horas</button>
            <button type="button" id="deleteEvent" class="btn btn-danger">Borrar cita</button>
        </div>
    </div>
</form>

<script type="module">
    import { errorAlert, successAlert, successAlertNoReload, infoAlert, loadingSpinner, loadingAlert, selectAlert, warningAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';
    import { fullCalendar } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fullcalendar/index.js';

    let api = '<?php echo $_ENV['BASE_URL']; ?>/api.php';
    let eventId = '<?php echo $eventId; ?>';

    $(function () {
        const $start = $('#startDetails');
        const $end = $('#endDetails');
        const $format = 'h:mm A';
        const $totalHours = $('#totalHours');

        sendFetch(api, 'POST', { action: 'getEventDetails', eventId: eventId })
            .then(async data => {
                if (data.success && data.confirmed === false) {
                    $start.val(data.data.start);
                    $end.val(data.data.end);
                    // Remover placeholders y mostrar inputs
                    $("#startPlaceholder, #endPlaceholder").remove();
                    $("#startDetails, #endDetails").removeClass("d-none");

                    await calcularHoras($start.val(), $end.val(), $totalHours, $format);
                } else if (data.success && data.confirmed === true) {
                    infoAlert(data.message);
                    $start.val(data.data.start);
                    $end.val(data.data.end);
                    // Remover placeholders y mostrar inputs
                    $("#startPlaceholder, #endPlaceholder").remove();
                    $("#startDetails, #endDetails").removeClass("d-none");
                    $("#startDetails, #endDetails").attr("disabled", true);
                    $("#totalHours").attr("disabled", true);
                    $("#totalHours").removeClass("text-center");
                    $("#btn_mas, #btn_menos").attr("hidden", true);
                    $("#confirmHours, #deleteEvent").attr("hidden", true);

                    await calcularHoras($start.val(), $end.val(), $totalHours, $format);
                    $('#eventDetails').modal('hide');
                }
                else {
                    errorAlert(data.message);
                    $('#eventDetails').modal('hide');
                }
            });

        const fixHours = fix => {
            // Obtener la hora actual y ajustar minutos
            const fixedHour = moment($totalHours.val(), 'HH:mm')
                .add(fix, 'minutes')
                .format('HH:mm');
            $totalHours.val(fixedHour);
        };

        // Evento click para ambos botones
        $('#btn_mas, #btn_menos').on('click', function () {
            const fix = parseInt($(this).data('fix'), 10);
            fixHours(fix);
        });

        $('#confirmHours').on('click', function () {
            let data = $('#confirmEventForm').serialize();
            data += `&eventId=${eventId}`;

            confirmHours(data);
            window.calendar.refetchEvents();
        });

        $('#deleteEvent').on('click', function () {
            let data = $('#confirmEventForm').serialize();
            data += `&eventId=${eventId}`;

            deleteEvent(data);
        });

        configurarTimepickers();

    });

    function configurarTimepickers() {
        var opcionesComunes = {
            'minTime': '09:00am',
            'maxTime': '06:00pm',
            'timeFormat': 'H:i',
            'showDuration': false
        };

        $('#startDetails, #endDetails').timepicker(opcionesComunes);
    }

    $('#endDetails').on('changeTime', function () {
        if ($('#startDetails').val() === '') {
            infoAlert("Por favor ingrese la hora de entrada");
        } else {
            reCalcularHoras();
        }
    });

    // Manejador de eventos para 'start'
    $('#startDetails').on('changeTime', function () {
        if ($('#endDetails').val()) {
            reCalcularHoras();
        }
    });

    //funcion para que al borrarse la hora de salida se borre la hora total
    $('#endDetails').on('keyup', function () {
        if ($('#endDetails').val() == '') {
            $('#totalHours').val('');
        }
    });

    const calcularHoras = async (startTime, endTime, totalHours, format) => {

        // Utilizando moment.js para parsear las horas
        var entrada = moment(startTime, format);
        var salida = moment(endTime, format);

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
            totalHours.val(horasTotales);
        }
    }

    const confirmHours = async (data) => {
        loadingAlert();
        sendFetch(api, 'POST', { action: 'confirmHours', hoursData: data })
            .then(async data => {
                if (data.success) {
                    successAlertNoReload(data.message);
                    $('#eventDetails').modal('hide');
                    window.calendar.refetchEvents();
                } else {
                    if (data.error != null) infoAlert(data.error);
                    errorAlert(data.message);
                }
            })

    }

    const deleteEvent = async (data) => {
        selectAlert(
            'Por favor selecciona el motivo de la cancelación',
            'Selecciona',
            {
                '1': 'Falta de asistencia',
                '2': 'Falta de notificada',
                '3': 'Otro'
            },
            "Confirmar",
            async (result) => {

                data += `&deleteRazon=${result}`;

                loadingAlert();

                sendFetch(api, 'POST', {
                    action: 'deleteEvent',
                    hoursData: data
                })
                    .then(async data => {

                        if (data.success) {

                            successAlert(data.message);

                            $('#eventDetails').modal('hide');

                            window.calendar.refetchEvents();

                        } else {

                            if (data.error != null)
                                infoAlert(data.error);

                            errorAlert(data.message);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        errorAlert(err.message);
                    });
            }
        );
    }

    function reCalcularHoras() {
        let start = $('#startDetails').val();
        let end = $('#endDetails').val();
        let format = 'h:mm A';

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