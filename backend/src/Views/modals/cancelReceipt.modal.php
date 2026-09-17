<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<label for="cancelReason" class="form-label">Especifica el motivo de cancelación <span class="text-danger">*</span></label>
<textarea class="form-control" id="cancelReason" rows="3" placeholder="Escribe el motivo..."></textarea>
<div class="text-danger small mt-1 d-none" id="cancelReasonError">El motivo es obligatorio.</div>
