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

$date = $_POST['date'] ?? NULL;

?>

<form action="" id="addEvent">
    <div class="form-group">
        <div class="mb-3">
            <label for="date">Fecha seleccionada: </label>
            <input class="form-control" id="date" name="date" type="text" placeholder="" value="<?php echo $date ?>" readonly>    
        </div>
        <div class="mb-3">
            <label for="student">Nombre del alumno: </label>
            <select class="form-select" id="student" name="student">
                <option selected value="0">Nombre</option>   
            </select>       
        </div>
        <div class="mb-3" hidden>
            <label for="studentName">Nombre del alumno: </label>
            <input class="form-control" id="studentName" name="studentName" type="text" placeholder="" value="" readonly>
        </div>
        <div class="mb-3">
            <label for="start">Hora de ingreso: </label>
            <input class="form-control" id="start" name="start" type="text" placeholder="" value="" >      
        </div>
        <div class="mb-3">
            <label for="end">Hora de salida: </label>
            <input class="form-control" id="end" name="end" type="text" placeholder="" value="" >      
        </div>        

        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-success">Registrar</button>
        </div>
    </div>
</form>

<script type="module">    
    import { errorAlert, successAlert, successAlertAuto, infoAlert, loadingSpinner, loadingAlert } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/fetchCall.js'
    import { fullCalendar } from '<?php echo $_ENV['BASE_URL']; ?>/public/js/global/fullcalendar/index.js';

    let api = '<?php echo $_ENV['BASE_URL']; ?>/public/api.php';

    $(function() {
        configurarTimepickers();

        getStudentsList($('#student'));
    }); 

    $('#addEvent').submit(function(e) {
        e.preventDefault();
        
        let eventData = $(this).serialize();

        loadingAlert();

        sendFetch(api, 'POST', { action: 'addEvent', eventData })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Ocurrió un error al realizar la petición: ' + response.statusText);
                    }
                    return response.json();  // Asegúrate de que se está retornando la promesa con la conversión a JSON
                })
                .then(data => {
                    if (data.success) {
                        successAlertAuto(data.message);
                        $('#addEventModal').modal('hide');
                        window.calendar.refetchEvents();
                    } else {
                        errorAlert(data.message);
                    }
                });        
    });

    function configurarTimepickers() {
        var opcionesComunes = {
            'minTime': '09:00am',
            'maxTime': '05:30pm',
            'timeFormat': 'H:i',
            'showDuration': false
        };

        $('#start, #end').timepicker(opcionesComunes);
    }

    // Manejador de eventos para 'hora_salida'
    $('#end').on('changeTime', function() {
        if ($('#start').val() === '') {
            infoAlert("Por favor ingrese la hora de entrada");
        }
    });

     // Manejador de eventos para 'start'
     $('#start').on('changeTime', function() {
        if ($('#end').val()) {
            
        }
    });

    //funcion para que al borrarse la hora de salida se borre la hora total
    $('#end').on('keyup', function() {
        if($('#end').val() == ''){
            $('#totalHours').val('');
        }
    });


    const getStudentsList = async (input) => {
        try {
            input.select2({
                dropdownParent: $('#addEventModal'),
                theme: "bootstrap-5",
                placeholder: 'Selecciona el paciente',
                ajax: {
                    url: api,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            action: 'getStudentsListSelect',
                            search: params.term, // término de búsqueda
                            page: params.page || 1
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        
                        return {
                            results: data.results,
                            pagination: data.pagination
                        };
                    },
                    cache: true
                },
                minimumInputLength: 2,
                language: {
                    inputTooShort: function() {
                        return "Por favor ingrese al menos 2 caracteres";
                    },
                    searching: function() {
                        return "Buscando...";
                    },
                    noResults: function() {
                        return "No se encontraron resultados.";
                    }
                },
            });

            input.on('select2:select', function(e) {
                let text = e.params.data.text;
                $('#studentName').val(text);
                //alert('Seleccionado: ' + selectedData.text);
                //$('#patientId').val(e.params.data.id);
            });

        } catch (error) {
            console.error('Error al inicializar la búsqueda de pacientes:', error);
        }
    };

</script>   