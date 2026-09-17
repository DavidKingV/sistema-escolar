<?php
if (!isset($date)) {
    http_response_code(404);
    exit;
}

$safeDate = htmlspecialchars((string) $date, ENT_QUOTES, 'UTF-8');
?>
<form action="" id="addEvent">
    <div class="form-group">
        <div class="mb-3">
            <label for="date" class="form-label">Fecha del evento <span class="text-danger">*</span></label>
            <input class="form-control" id="date" name="date" type="date" value="<?= $safeDate ?>"
                <?= $date !== '' ? 'readonly' : '' ?>>
        </div>
        <div class="mb-3">
            <label for="student" class="form-label">Nombre del alumno <span class="text-danger">*</span></label>
            <select class="form-select" id="student" name="student"><option selected value="0">Nombre</option></select>
        </div>
        <div class="mb-3" hidden>
            <label for="studentName">Nombre del alumno:</label>
            <input class="form-control" id="studentName" name="studentName" type="text" readonly>
        </div>
        <div class="mb-3">
            <label for="start" class="form-label">Hora de ingreso <span class="text-danger">*</span></label>
            <input class="form-control" id="start" name="start" type="text">
        </div>
        <div class="mb-3">
            <label for="end" class="form-label">Hora de salida <span class="text-danger">*</span></label>
            <input class="form-control" id="end" name="end" type="text">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            <button type="submit" class="btn btn-success">Registrar</button>
        </div>
    </div>
</form>
