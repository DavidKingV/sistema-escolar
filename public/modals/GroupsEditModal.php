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

$groupId = $_POST['groupId'] ?? null;
if (!$groupId) {
    echo '<div class="alert alert-warning" role="alert">
    ID de grupo no proporcionado.
    </div>';
    exit();
}
?>
<form id="updateGroup">
    <div class="row g-2 py-1">
        <div class="col-md" hidden>
            <div class="form-floating">
                <input type="text" class="form-control" id="idGroupDB" name="idGroupDB" readonly>
                <label for="idGroupDB">ID</label>
            </div>
            <input type="text" class="form-control" id="idCarreerHidden" name="idCarreerHidden" readonly>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <select class="form-select" id="carreerNameGroupEdit" name="carreerNameGroupEdit"
                    aria-label="Floating label select example">
                    <option selected value="0">Selecciona una opción</option>
                </select>
                <label for="floatingSelect">Carrera</label>
            </div>
            <label id="carreerNameGroupEdit-error" class="error text-bg-danger" for="carreerNameGroupEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <input type="text" class="form-control" id="keyGroupEdit" name="keyGroupEdit" value="">
                <label for="keyGroupEdit">Clave del grupo <span class="text-danger">*</span></label>
            </div>
            <label id="keyGroupEdit-error" class="error text-bg-danger" for="keyGroupEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <input type="text" class="form-control" id="nameGroupEdit" name="nameGroupEdit" value="">
                <label for="nameGroupEdit">Nombre del grupo <span class="text-danger">*</span></label>
            </div>
            <label id="nameGroupEdit-error" class="error text-bg-danger" for="nameGroupEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
    </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating">
                <input type="date" class="form-control" id="startDateEdit" name="startDateEdit" value="">
                <label for="startDateEdit">Fecha de inicio <span class="text-danger">*</span></label>
            </div>
            <label id="startDateEdit-error" class="error text-bg-danger" for="startDateEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <input type="date" class="form-control" id="endDateEdit" name="endDateEdit" value="">
                <label for="endDateEdit">Fecha de término <span class="text-danger">*</span></label>
            </div>
            <label id="endDateEdit-error" class="error text-bg-danger" for="endDateEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
    </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating">
                <input type="text" class="form-control" id="descriptionGroupEdit" name="descriptionGroupEdit" value="">
                <label for="descriptionGroupEdit">Descripción <span class="text-danger">*</span></label>
            </div>
            <label id="descriptionGroupEdit-error" class="error text-bg-danger" for="descriptionGroupEdit"
                style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="saveGroupChanges" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>

<script type="module">
    import { errorAlert, successAlert, successAlertAuto, infoAlert, loadingSpinner, loadingAlert } from '<?php echo $_ENV['BASE_URL']; ?>/js/global/alerts.js';
    import { FillTable, CleanInputsGroupsEdit, FillDivsGroups } from '<?php echo $_ENV['BASE_URL']; ?>/js/groups/forms.js';
    import { handleUpdateGroup } from '<?php echo $_ENV['BASE_URL']; ?>/js/groups/index.js';
    import { initUpdateGroupValidation } from '<?php echo $_ENV['BASE_URL']; ?>/js/utils/validate.js';
    import { createFormObserver, serializeForm } from '<?php echo $_ENV['BASE_URL']; ?>/js/utils/formChanges.js';

    const groupObserver = createFormObserver({
        form: "#updateGroup",
        saveButton: "#saveGroupChanges",
        getCurrentData: () => serializeForm("#updateGroup"),
    });

    groupObserver.observe();

    const groupId = <?php echo json_encode($groupId); ?>;

    $(async function () {
        await GetDataGroupEdit(groupId);

        // 3. Inicializar validación
        initUpdateGroupValidation(handleUpdateGroup);
    });

    const GetDataGroupEdit = async (groupId) => {
        try {
            // Función para obtener el valor predeterminado de la base de datos usando async/await
            const getDefaultCareer = async () => {
                const response = await $.ajax({
                    url: `${BASE_URL}/group/getGroupById`,
                    type: 'GET',
                    data: { groupId: groupId },
                });
                if (!response.success) {
                    throw new Error(response.message);
                } else {
                    FillTable(response.data);
                    return response.data.carreer_name;
                }
            };

            // Función para cargar el JSON de carreras
            const loadCarreers = async () => {
                const response = await $.ajax({
                    url: `${BASE_URL}/group/getCarreersForGroupCreation`,
                    type: 'GET'
                });
                if (!response.success) {
                    throw new Error(response.message);
                } else {
                    return response.data;
                }

            };

            // Obtener el valor predeterminado
            const defaultCareer = await getDefaultCareer();

            // Cargar el JSON de carreras
            const carreers = await loadCarreers();

            let $selectEdit = $('#carreerNameGroupEdit');
            $.each(carreers, function (area, subareas) {
                let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });
                $.each(subareas, function (subarea, programs) {
                    let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                    $.each(programs, function (index, program) {
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

            groupObserver.saveSnapshot();

        } catch (error) {
            console.error('Error: ', error);
        } finally {
            // Cierra loader
            $("#groupEditLoader").hide();
        }
    };
</script>