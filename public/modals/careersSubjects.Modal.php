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

$careerId = $_POST['careerId'];
?>

<ul class="nav nav-tabs" id="myTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#add_subject_tab" type="button" role="tab" aria-controls="add_subject_tab" aria-selected="true">Registrar</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#view_subjects_tab" type="button" role="tab" aria-controls="view_subjects_tab" aria-selected="false">Ver Lista</button>
    </li>
</ul>

<div class="tab-content" id="myTabContent">
    <div class="tab-pane fade show active py-4" id="add_subject_tab" role="tabpanel">
        <form id="addSubjectCareer">
            <!--<input type="text" id="carreerId" name="carreerId" value="" hidden>-->
            <div class="col-md">
                <div class="form-floating">
                    <select class="form-select subjectName" name="subjectName" id="subjectName">
                        <option selected value="0">Materia</option>
                    </select>
                    <label for="floatingSelect">Selecciona</label>
                </div>
                <label id="subjectName-error" class="error text-bg-danger" for="subjectName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
            </div>

            <div class="col-md" id="childSubjectDiv">
                <div class="form-floating">
                    <select class="form-select childSubjectName" name="childSubjectName" id="childSubjectName" disabled>
                        <option selected value="">Submateria</option>
                    </select>
                    <label for="floatingSelect">Selecciona</label>
                </div>
                <label id="childSubjectName-error" class="error text-bg-danger" for="childSubjectName" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
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
    import { errorAlert, successAlert, infoAlert, loadingSpinner, loadingAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/utils/alerts.js';
    import { sendFetch } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/fetchCall.js';
    import { initializeDataTable } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/dataTables.js';

    let api = '<?php echo $_ENV['BASE_URL']; ?>/api.php';
    let careerId = <?php echo $careerId; ?>;

    $(function() {
        getSubjectsList($('#subjectName'));
    });

    $('#profile-tab').on('click', function() {
        initializeDataTable('#subjectsListTable', api, { careerId: careerId, action: 'subjectsListTable' }, [
            { data: 'claveSubject', render: function(data, type, row) {
            return `<div>
                        <div class="ps-3">
                            <div class="fw-600 pb-1">`+row.claveSubject+`</div>
                            <p class="m-0 text-grey fs-09">`+row.claveSubjectChild+`</p>
                        </div>
                    </div>`;
            }, 'className': 'text-center py-2'}, 
            { data: 'nombre', render: function(data, type, row) {
            return `<div>
                        <div class="ps-3">
                            <div class="fw-600 pb-1">`+row.nombre+`</div>
                            <p class="m-0 text-grey fs-09">`+row.subject_child_nombre+`</p>
                        </div>
                    </div>`;
            }, 'className': 'text-center py-2'}
        ]);
    });

    const getSubjectsList = async (input) => {
        try {
            input.select2({
                dropdownParent: $('#subjectsModal'),
                theme: "bootstrap-5",
                placeholder: 'Selecciona una materia',
                ajax: {
                    url: api,
                    type: 'POST',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {                        
                        return {
                            action: 'getSubjectsListSelect',
                            careerId: careerId,
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
                minimumInputLength: 0,
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
                GetChildSubject(e.params.data.id)
            });

            input.on('select2:unselect', function(e) {
                $('#childSubjectName').empty();
                $('#childSubjectName').append('<option selected value="">Submateria</option>');
                $('#childSubjectName').prop("disabled", true);
            });

            input.on('select2:clear', function(e) {
                $('#childSubjectName').empty();
                $('#childSubjectName').append('<option selected value="">Submateria</option>');
                $('#childSubjectName').prop("disabled", true);
            });

            input.on('change', function(e) {
                $('#childSubjectName').empty();
                $('#childSubjectName').append('<option selected value="">Submateria</option>');
                $('#childSubjectName').prop("disabled", true);
            });

        } catch (error) {
            console.error('Error al inicializar la búsqueda de materias:', error);
        }
    };

    const GetChildSubject = async (subjectId) => {
 
        const Childsubject = async () => {
            try{
                const response = await $.ajax({
                    url: api,
                    type: 'POST',
                    data: {
                        action: 'getChildSubject',
                        subjectId: subjectId
                    }
                });
                return response;
            }catch(error){
                console.error(error);
            }
        }

        try{
            const  subjectsList = await Childsubject();
            if (subjectsList[0].success === false) {
                return;
            }

            let $select = $('.childSubjectName');

            $.each(subjectsList, function(index, subject) {
                if (subject.success !== false) {
                    let $option = $('<option>', {
                        value: subject.childSubjectId,
                        text: subject.childSubjectName
                    });

                    $select.append($option);
                    
                }
            });

            $select.select2({
                dropdownParent: $("#subjectsModal"),
                theme: "bootstrap-5",
                placeholder: 'Selecciona la submateria',
            });
            
            $("#childSubjectName").prop("disabled", false);
        }catch(error){
            console.error(error);
        }

    } 

    $("#addSubjectCareer").on("submit", function(e){
    e.preventDefault();

    let subjectAddData = $(this).serialize();
    subjectAddData += '&careerId=' + careerId;

    loadingAlert();

    sendFetch(api, 'POST', { action: 'addSubjectCareer', subjectAddData })
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
                        $('#subjectsModal').modal('hide');
                        $('#carreersTable').DataTable().ajax.reload();
                    } else {
                        errorAlert(data.message);
                    }
                });
    });

</script>