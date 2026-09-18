<?php
if (!isset($studentId, $subjectId, $subjectName, $gradeId)) {
    http_response_code(404);
    exit;
}

$escape = static fn ($value): string => htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
?>
<form id="addMakeOverGrade">
    <input type="hidden" name="studentId" value="<?= (int) $studentId ?>">
    <input type="hidden" name="subjectId" value="<?= (int) $subjectId ?>">
    <input type="hidden" name="subjectChildId" value="<?= $subjectChildId === null ? '' : (int) $subjectChildId ?>">
    <input type="hidden" name="gradeId" value="<?= (int) $gradeId ?>">
    <div class="mb-3"><label for="makeOverSubjectName" class="form-label">Materia</label><input type="text" class="form-control" id="makeOverSubjectName" value="<?= $escape($subjectName) ?>" disabled></div>
    <div class="mb-3"><label for="makeOverSubjectChildName" class="form-label">Submateria</label><input type="text" class="form-control" id="makeOverSubjectChildName" value="<?= $escape($subjectChildName ?? '') ?>" disabled></div>
    <div class="mb-3"><label for="continuosGrade" class="form-label">Calificación continua</label><input type="text" class="form-control" id="continuosGrade" name="continuosGrade"></div>
    <div class="mb-3"><label for="examGrade" class="form-label">Examen</label><input type="text" class="form-control" id="examGrade" name="examGrade"></div>
    <div class="mb-3"><label for="finalGrade" class="form-label">Calificación final</label><input type="text" class="form-control" id="finalGrade" name="finalGrade" readonly></div>
    <div class="col-md"><button type="submit" class="btn btn-success">Agregar</button></div>
</form>
