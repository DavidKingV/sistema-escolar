<?php
if (!isset($eventId)) {
    http_response_code(404);
    exit;
}
?>
<form action="" id="confirmEventForm" data-event-id="<?= htmlspecialchars((string) $eventId, ENT_QUOTES, 'UTF-8') ?>">
    <div class="form-group">
        <div class="mb-3">
            <label for="startDetails">Hora de ingreso:</label>
            <p class="placeholder-glow" id="startPlaceholder"><span class="placeholder col-12"></span></p>
            <input class="form-control d-none" id="startDetails" name="startDetails" type="text">
        </div>
        <div class="mb-3">
            <label for="endDetails">Hora de salida:</label>
            <p class="placeholder-glow" id="endPlaceholder"><span class="placeholder col-12"></span></p>
            <input class="form-control d-none" id="endDetails" name="endDetails" type="text">
        </div>
        <div class="mb-3">
            <label for="totalHours" class="form-label">Horas totales:</label>
            <div class="input-group">
                <button type="button" id="btn_menos" data-fix="-30" class="btn btn-danger">-</button>
                <input type="text" name="totalHours" id="totalHours" class="form-control text-center" readonly>
                <button type="button" id="btn_mas" data-fix="30" class="btn btn-success">+</button>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="button" id="confirmHours" class="btn btn-success">Confirmar Horas</button>
            <button type="button" id="deleteEvent" class="btn btn-danger">Borrar cita</button>
        </div>
    </div>
</form>
