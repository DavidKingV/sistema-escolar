<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<div class="table-responsive">
    <div class="col-md d-none"><div class="form-floating"><input type="text" class="form-control" id="idStudentDB" name="idStudentDB" readonly><label for="idStudentDB">ID</label></div></div>
    <table class="table table-bordered table-striped" id="paymentHistoryStudentTable">
        <thead><tr><th>Concepto</th><th>Monto</th><th>Fecha de registro</th><th>Método de pago</th><th>Factura</th><th>Estatus</th><th>Acciones</th></tr></thead>
        <tbody></tbody>
    </table>
</div>
