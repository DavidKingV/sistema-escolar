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

$groupId = $_POST['groupId'] ?? null;
if (!$groupId) {
    echo '<div class="alert alert-warning" role="alert">
    ID de grupo no proporcionado.
    </div>';
    exit();
}
?>
<form id="updateGroup">
    <div class="row g-2">
        <div class="col-md" hidden>
            <div class="form-floating">
            <input type="text" class="form-control" id="idGroupDB" name="idGroupDB" readonly>
            <label for="idGroupDB">ID</label>
        </div>
        <input type="text" class="form-control" id="idCarreerHidden" name="idCarreerHidden" readonly>
    </div>
    <div class="col-md">
        <div class="form-floating">                   
            <select class="form-select" id="carreerNameGroupEdit" name="carreerNameGroupEdit"  aria-label="Floating label select example">
                <option selected value="0">Carrera</option>   
            </select>
            <label for="floatingSelect">Selecciona</label>
        </div>
        <label id="carreerNameGroupEdit-error" class="error text-bg-danger" for="carreerNameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
    </div>
    <div class="col-md">
        <div class="form-floating">
            <input type="text" class="form-control" id="keyGroupEdit" name="keyGroupEdit" value="">
            <label for="keyGroupEdit">Clave del grupo</label>
        </div>
        <label id="keyGroupEdit-error" class="error text-bg-danger" for="keyGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
    </div>
    <div class="col-md">
        <div class="form-floating">
            <input type="text" class="form-control" id="nameGroupEdit" name="nameGroupEdit" value="">
                <label for="nameGroupEdit">Nombre</label>
                    </div>
                    <label id="nameGroupEdit-error" class="error text-bg-danger" for="nameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                </div>
            </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating">
                <input type="date" class="form-control" id="startDateEdit" name="startDateEdit" value="">
                <label for="startDateEdit">Fecha de Inicio</label>
            </div>
            <label id="startDateEdit-error" class="error text-bg-danger" for="startDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <input type="date" class="form-control" id="endDateEdit" name="endDateEdit" value="">
                <label for="endDateEdit">Fecha de Termino</label>
            </div>
            <label id="endDateEdit-error" class="error text-bg-danger" for="endDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
    </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating">
                <input type="text" class="form-control" id="descriptionGroupEdit" name="descriptionGroupEdit" value="">
                <label for="descriptionGroupEdit">Descripción</label>
            </div>
            <label id="descriptionGroupEdit-error" class="error text-bg-danger" for="descriptionGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>                
    </div>
    </div>        
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>

<script type="module">
    import { errorAlert, successAlert, successAlertAuto, infoAlert, loadingSpinner, loadingAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { FillTable, CleanInputsGroupsEdit, FillDivsGroups } from '<?php echo $_ENV['BASE_URL']; ?>/js/groups/forms.js';


    const groupId = <?php echo json_encode($groupId); ?>;
    
    const hideLoader = () => $("#globalLoader").fadeOut(200);

    $(async function() {
        await GetDataGroupEdit(groupId);
    });
    
    const GetDataGroupEdit = async (groupId) => {
    try {
        // Función para obtener el valor predeterminado de la base de datos usando async/await
        const getDefaultCareer = async () => {
            const response = await $.ajax({
                url: '../backend/groups/routes.php',
                type: 'GET',
                data: {groupId: groupId, action: 'getGroupData'},
            });
            if (!response.success) {
                throw new Error(response.message);
            } else {
                await FillTable(response);
                return response.carreer_name;               
            }
        };

        // Función para cargar el JSON de carreras
        const loadCareers = async () => {
            const response = await $.ajax({
                url: '../backend/groups/routes.php',
                type: 'GET',
                data: {action: 'getGroupsJson'}
            });
            if (!response) {
                throw new Error(response.message);
            } else {
                return response;
            }
            
        };

        // Obtener el valor predeterminado
        const defaultCareer = await getDefaultCareer();

        // Cargar el JSON de carreras
        const careers = await loadCareers();

        let $selectEdit = $('#carreerNameGroupEdit');
        $.each(careers, function(area, subareas) {
            let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });
            $.each(subareas, function(subarea, programs) {
                let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                $.each(programs, function(index, program) {
                    let $option = $('<option>', {
                        value: program.id,
                        text: '    ' + program.nombre                       
                    });
                    // Verificar si esta opción coincide con el valor predeterminado
                    if (program.nombre === defaultCareer) {
                        $option.prop('selected', true); // Establecer la opción como seleccionada
                    }

                    $subOptgroup.append($option); // Agrega la opción al subgrupo
                });
                $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
            });

            $selectEdit.append($mainOptgroup);
        });

        // Inicializar Select2
        $selectEdit.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#GroupsEditModal')
        });

    } catch (error) {
        console.error('Error: ', error);
    }finally {
        hideLoader();
    }
};
</script>