<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<p class="text-muted mb-3">Selecciona el grupo correcto para <strong id="duplicateStudentName"></strong>. Los demás grupos de carrera serán eliminados.</p>
<div id="duplicateGroupsList"></div>
<div class="alert alert-warning mt-3" role="alert"><i class="bi bi-info-circle"></i> Esta acción eliminará al alumno de todos los grupos de carrera excepto el seleccionado. Los cursos y diplomados no se verán afectados.</div>
