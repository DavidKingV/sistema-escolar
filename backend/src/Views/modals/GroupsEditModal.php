<?php
if (!isset($groupId)) {
    http_response_code(404);
    exit;
}
?>
<form id="updateGroup" data-group-id="<?= (int) $groupId ?>">
    <div class="row g-2 py-1">
        <div class="col-md" hidden>
            <div class="form-floating"><input type="text" class="form-control" id="idGroupDB" name="idGroupDB" readonly><label for="idGroupDB">ID</label></div>
            <input type="text" class="form-control" id="idCarreerHidden" name="idCarreerHidden" readonly>
        </div>
        <div class="col-md">
            <div class="form-floating">
                <select class="form-select" id="carreerNameGroupEdit" name="carreerNameGroupEdit"><option selected value="0">Selecciona una opción</option></select>
                <label for="carreerNameGroupEdit">Carrera</label>
            </div>
            <label id="carreerNameGroupEdit-error" class="error text-bg-danger" for="carreerNameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating"><input type="text" class="form-control" id="keyGroupEdit" name="keyGroupEdit"><label for="keyGroupEdit">Clave del grupo <span class="text-danger">*</span></label></div>
            <label id="keyGroupEdit-error" class="error text-bg-danger" for="keyGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating"><input type="text" class="form-control" id="nameGroupEdit" name="nameGroupEdit"><label for="nameGroupEdit">Nombre del grupo <span class="text-danger">*</span></label></div>
            <label id="nameGroupEdit-error" class="error text-bg-danger" for="nameGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
    </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating"><input type="date" class="form-control" id="startDateEdit" name="startDateEdit"><label for="startDateEdit">Fecha de inicio <span class="text-danger">*</span></label></div>
            <label id="startDateEdit-error" class="error text-bg-danger" for="startDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
        <div class="col-md">
            <div class="form-floating"><input type="date" class="form-control" id="endDateEdit" name="endDateEdit"><label for="endDateEdit">Fecha de término <span class="text-danger">*</span></label></div>
            <label id="endDateEdit-error" class="error text-bg-danger" for="endDateEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
    </div>
    <div class="row g-2 py-1">
        <div class="col-md">
            <div class="form-floating"><input type="text" class="form-control" id="descriptionGroupEdit" name="descriptionGroupEdit"><label for="descriptionGroupEdit">Descripción <span class="text-danger">*</span></label></div>
            <label id="descriptionGroupEdit-error" class="error text-bg-danger" for="descriptionGroupEdit" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="saveGroupChanges" class="btn btn-primary">Guardar Cambios</button>
    </div>
</form>
