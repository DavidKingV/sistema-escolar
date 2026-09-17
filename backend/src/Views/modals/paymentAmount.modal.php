<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="row g-2"><div class="col-md">
    <label for="studentAmount">Costo de la mensualidad</label>
    <label id="studentAmount-error" class="error text-bg-danger" for="studentAmount"></label>
    <div class="input-group mb-3 py-2"><span class="input-group-text">$</span><input type="text" class="form-control" id="studentAmount" name="studentAmount"></div>
</div></div>
