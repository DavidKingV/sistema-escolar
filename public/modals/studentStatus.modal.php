<?php
if (!isset($studentId, $studentStatus)) {
    http_response_code(404);
    exit;
}
?>

<div class="form-group">
    <div class="mb-3" hidden>
        <label for="studentId">ID:</label>
        <input
            class="form-control"
            id="studentId"
            name="studentId"
            type="text"
            value="<?php echo htmlspecialchars((string) $studentId, ENT_QUOTES, 'UTF-8'); ?>"
            readonly
        >
    </div>

    <div class="mb-3">
        <label for="studentStatus">Estatus:</label>
        <select name="studentStatus" id="studentStatus" class="form-control">
            <option value="0">Selección</option>
            <option value="1" <?php echo $studentStatus === 1 ? 'selected' : ''; ?>>Activo</option>
            <option value="2" <?php echo $studentStatus === 2 ? 'selected' : ''; ?>>Baja Temporal</option>
            <option value="3" <?php echo $studentStatus === 3 ? 'selected' : ''; ?>>Inactivo</option>
            <option value="4" <?php echo $studentStatus === 4 ? 'selected' : ''; ?>>Egresado</option>
        </select>
    </div>

    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" id="updateStudentStatus" class="btn btn-success">Actualizar</button>
    </div>
</div>
