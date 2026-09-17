<?php if (($__modalView ?? false) !== true) { http_response_code(404); exit; } ?>
<input type="hidden" id="idPayment" name="idPayment" readonly>
<div class="row g-2 py-1"><div class="col-12"><label class="text-muted small">Concepto de pago</label><p class="form-control-plaintext fw-bold ms-1" id="paymentConcept"></p></div></div>
<div class="row g-2 py-1"><div class="col-12"><label class="text-muted small">Tipo de comprobante</label><p class="form-control-plaintext fw-bold ms-1" id="paymentInvoice"></p></div></div>
<hr class="my-1">
<div class="row g-2 py-1 align-items-end">
    <div class="col-md-4"><label for="paymentPrice" class="form-label">Monto</label><div class="input-group"><span class="input-group-text">$</span><input type="text" class="form-control" id="paymentPrice" name="paymentPrice"></div></div>
    <div class="col-md-4"><div class="d-flex align-items-center gap-2 mb-1"><label for="paymentExtra" class="form-label mb-0">Recargo</label><input class="form-check-input" type="checkbox" id="paymentExtraCkeck" name="paymentExtraCkeck" checked></div><div class="input-group"><span class="input-group-text">$</span><input type="text" class="form-control" id="paymentExtra" name="paymentExtra"></div></div>
    <div class="col-md-4"><label for="paymentTotal" class="form-label">Total</label><div class="input-group"><span class="input-group-text">$</span><input type="text" class="form-control fw-bold" id="paymentTotal" name="paymentTotal" readonly></div></div>
</div>
<div class="row g-2 py-1"><div class="col-12"><label for="paymentMethod" class="form-label">Método de pago</label><select class="form-select" id="paymentMethod" name="paymentMethod"><option value="0">Selecciona un método</option><option value="1">Efectivo</option><option value="3">Transferencia bancaria</option><option value="4">Tarjeta de crédito</option><option value="28">Tarjeta de débito</option></select></div></div>
<div class="row g-2 py-1"><div class="col-12"><label for="paymentComments" class="form-label">Comentarios</label><textarea class="form-control" id="paymentComments" name="paymentComments" placeholder="Comentarios sobre el pago" rows="2"></textarea></div></div>
