<?php
if (!isset($makeOverId)) {
    http_response_code(404);
    exit;
}
?>
<div id="makeOverDetails" data-make-over-id="<?= (int) $makeOverId ?>">
    <div class="mb-3"><label for="viewMakeOverSubjectName" class="form-label">Materia</label><input type="text" class="form-control" id="viewMakeOverSubjectName" disabled></div>
    <div class="mb-3"><label for="viewMakeOverSubjectChildName" class="form-label">Submateria</label><input type="text" class="form-control" id="viewMakeOverSubjectChildName" disabled></div>
    <div class="mb-3"><label for="viewMakeOverContinuosGrade" class="form-label">Calificación continua</label><input type="text" class="form-control" id="viewMakeOverContinuosGrade" disabled></div>
    <div class="mb-3"><label for="viewMakeOverExamGrade" class="form-label">Examen</label><input type="text" class="form-control" id="viewMakeOverExamGrade" disabled></div>
    <div class="mb-3"><label for="viewMakeOverFinalGrade" class="form-label">Calificación final</label><input type="text" class="form-control" id="viewMakeOverFinalGrade" disabled></div>
</div>
