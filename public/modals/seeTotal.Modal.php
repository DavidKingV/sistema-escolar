<?php
if (!isset($studentId, $totalHours)) {
    http_response_code(404);
    exit;
}
?>
<div id="studentHoursContent" data-student-id="<?= (int) $studentId ?>">
    <div class="text"><h5>Total de horas prácticas: <?= htmlspecialchars((string) $totalHours, ENT_QUOTES, 'UTF-8') ?></h5></div>
    <hr class="border-top">
    <div class="card-body">
        <table id="studentHoursDataTable" class="table table-bordered table-hover table-responsive">
            <thead><tr><th>Fecha</th><th>Horas del día</th><th>Status</th><th>Acciones</th></tr></thead>
            <tbody></tbody>
        </table>
    </div>
</div>
