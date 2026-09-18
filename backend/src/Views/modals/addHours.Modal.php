<?php
if (!isset($studentId)) {
    http_response_code(404);
    exit;
}
?>
<form id="addHoursForm" data-student-id="<?= (int) $studentId ?>">
    <div class="text-center mb-3"><h4 id="studentNameH" class="fw-bold text-primary">Nombre del alumno</h4></div>
    <div class="mb-3">
        <label for="date" class="form-label">📅 Fecha a registrar</label>
        <input type="date" name="date" id="date" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="start" class="form-label">⏰ Hora de entrada</label>
        <div class="input-group"><span class="input-group-text"><i class="bi bi-clock"></i></span><input type="text" name="start" id="start" class="form-control" placeholder="Ej. 08:00"></div>
    </div>
    <div class="mb-3">
        <label for="end" class="form-label">⏰ Hora de salida</label>
        <div class="input-group"><span class="input-group-text"><i class="bi bi-clock-history"></i></span><input type="text" name="end" id="end" class="form-control" placeholder="Ej. 14:00"></div>
    </div>
    <div class="mb-3">
        <label for="totalHours" class="form-label">⏳ Horas totales</label>
        <div class="input-group">
            <button type="button" id="btn_menos" data-ajuste="-30" class="btn btn-outline-danger"><i class="bi bi-dash-lg"></i></button>
            <input type="text" name="totalHours" id="totalHours" class="form-control text-center fw-bold" readonly>
            <button type="button" id="btn_mas" data-ajuste="30" class="btn btn-outline-success"><i class="bi bi-plus-lg"></i></button>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary">Guardar horas</button>
    </div>
</form>
