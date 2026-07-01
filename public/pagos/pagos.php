<?php include_once __DIR__ . '../../../backend/views/mainMenu.php'; ?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <title>Pagos</title>
</head>

<body>
    <!-- Content -->
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de pagos por estudiante</h2>
            </div>
            <div class="card">
                <div class="card-header">
                    <i class="fas fa-users"></i> Estudiantes Con Pagos Registrados
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="studentPaymentTable">
                            <thead>
                                <tr>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">No. Control</th>
                                    <th class="text-center">Programa</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Los datos se llenarán dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</html>

<!-- Modal Payments Details -->
<div class="modal fade modal-lg" id="studentPaymentsModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="studentPaymentsModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="studentPaymentsModalLabel"></h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="table-responsive">
                    <div class="col-md" hidden>
                        <div class="form-floating">
                            <input type="text" class="form-control" id="idStudentDB" name="idStudentDB" readonly hidden>
                            <label for="idStudentDB">ID</label>
                        </div>
                    </div>

                    <!-- Tabla que DataTables va a manejar -->
                    <table class="table table-bordered table-striped" id="paymentHistoryStudentTable">
                        <thead>
                            <tr>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Fecha de registro</th>
                                <th>Método de pago</th>
                                <th>Factura</th>
                                <th>Estatus</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="modal fade modal-lg" id="studentPaymentEditModal" data-bs-backdrop="static" tabindex="-1"
    aria-labelledby="studentPaymentEditModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="studentPaymentEditModalLabel"></h1>
                <button type="button" class="btn-close close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="updatePaymentStudent">
                    <input type="text" id="idPayment" name="idPayment" hidden readonly>

                    <div class="row g-2 py-1">
                        <div class="col-12">
                            <label class="text-muted small">Concepto de pago</label>
                            <p class="form-control-plaintext fw-bold ms-1" id="paymentConcept"></p>
                        </div>
                    </div>

                     <div class="row g-2 py-1">
                        <div class="col-12">
                            <label class="text-muted small">Tipo de comprobante</label>
                            <p class="form-control-plaintext fw-bold ms-1" id="paymentInvoice"></p>
                        </div>
                    </div>

                    <hr class="my-1">

                    <div class="row g-2 py-1 align-items-end">
                        <div class="col-md-4">
                            <label for="paymentPrice" class="form-label">Monto</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="paymentPrice" name="paymentPrice">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <label for="paymentExtra" class="form-label mb-0">Recargo</label>
                                <input class="form-check-input" type="checkbox" id="paymentExtraCkeck"
                                    name="paymentExtraCkeck" checked>
                            </div>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" id="paymentExtra" name="paymentExtra">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label for="paymentTotal" class="form-label">Total</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control fw-bold" id="paymentTotal" name="paymentTotal"
                                    readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row g-2 py-1">
                        <div class="col-12">
                            <label for="paymentMethod" class="form-label">Método de pago</label>
                            <select class="form-select" id="paymentMethod" name="paymentMethod">
                                <option selected value="0">Selecciona un método</option>
                                <option value="1">Efectivo</option>
                                <option value="3">Transferencia bancaria</option>
                                <option value="4">Tarjeta de crédito</option>
                                <option value="28">Tarjeta de débito</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2 py-1">
                        <div class="col-12">
                            <label for="paymentComments" class="form-label">Comentarios</label>
                            <textarea class="form-control" id="paymentComments" name="paymentComments"
                                placeholder="Comentarios sobre el pago" rows="2"></textarea>
                        </div>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-warning" id="cancelReceipt">Cancelar Recibo</button>
                <button type="submit" class="btn btn-primary" id="savePaymentChanges">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cancelReceiptModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Razón de cancelación</h5>
            </div>
            <div class="modal-body">
                <label for="cancelReason" class="form-label">
                    Especifica el motivo de cancelación <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="cancelReason" rows="3" placeholder="Escribe el motivo..."></textarea>
                <div class="text-danger small mt-1 d-none" id="cancelReasonError">
                    El motivo es obligatorio.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelReasonBack">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmCancelReceipt">Confirmar cancelación</button>
            </div>
        </div>
    </div>
</div>

<!-- Boostrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"
    integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI"
    crossorigin="anonymous"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4="
    crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js"
    integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>

<!-- datables -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.js"></script>

<!-- globaljs -->
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/students/index.js"></script>
<script type="module" src="../js/payments/index.js"></script>
<!--<script type="module" src="public/js/students.js"></script>-->
<script type="module" src="../js/utils/sessions.js"></script>