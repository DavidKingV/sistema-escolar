<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <link rel="stylesheet" href="../assets/css/jquery-ui.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Registrar pago</title>
</head>
<body>

    <?php include_once __DIR__.'/../../backend/views/mainMenu.php'; ?>
      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Agregar nuevo pago</h2>
                <a href="#" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la lista
                </a>
            </div>

            <!-- Días de pago del alumno -->
            <div class="card border-primary shadow mt-4" id="paymentDaysCard">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Días de pago del alumno</h6>
                </div>
                <div class="card-body">
                    <form id="studentPaymentDate">
                        <input type="text" class="form-control" id="studentId" name="studentId" hidden>
                        <div class="col-md">
                            <div class="mb-3">
                                <label id="paymentDay-error" class="error text-bg-danger" for="paymentDay" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <div class="input-group py-3">
                                    <span class="input-group-text">Cada mes los días</span>
                                    <input type="number" class="form-control" id="paymentDay" name="paymentDay">
                                </div>
                                <div class="input-group py-3">
                                    <span class="input-group-text">Concepto</span>
                                    <select class="form-select" id="paymentConceptDay" name="paymentConceptDay" aria-label="Floating label select example" readonly>
                                        <option selected value="Mensualidad">Mensualidad</option>
                                    </select>
                                </div>
                                <div class="input-group py-3">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" id="paymentAmountDay" name="paymentAmountDay">
                                </div>
                            </div>
                        </div>
                        <div class="row g-2 py-3">
                            <div class="col-md">
                                <button type="button" id="savePaymentDays" class="btn btn-success">Definir</button>
                                <button type="button" class="btn btn-primary" id="updatePaymentDays" disabled>Actualizar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Registrar pago -->
            <div class="card border-primary shadow mt-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Registrar</h6>
                </div>
                <div class="card-body">
                    <form id="paymentsForm">
                        <div class="row g-2 py-3">
                            <div class="col-md">
                                <div class="mb-3">
                                    <label for="studentName">Selecciona</label>                                    
                                    <select class="form-select" id="studentName" name="studentName" aria-label="Floating label select example">
                                        <option selected value="0">Nombre del estudiante</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md">
                                <label for="todayDate">Fecha actual</label>
                                <input class="form-check-input" type="checkbox" id="todayDate" name="todayDate" checked>                                
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control" id="paymentDate" name="paymentDate">
                                </div>
                            </div>
                            <div class="col-md">
                                <label for="paymentConcept">Concepto</label>
                                <label id="paymentConcept-error" class="error text-bg-danger" for="paymentConcept" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                <select class="form-select" id="paymentConcept" name="paymentConcept" aria-label="Floating label select example">
                                    <option selected value="0">Concepto</option>
                                    <option value="Inscripción">Inscripción</option>
                                    <option value="Mensualidad">Mensualidad</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="paymentMonth">Mes</label>                                
                                <select class="form-select" id="paymentMonth" name="paymentMonth" aria-label="Floating label select example">
                                    <option selected value="0">Mes</option>
                                    <option value="Enero">Enero</option>
                                    <option value="Febrero">Febrero</option>
                                    <option value="Marzo">Marzo</option>
                                    <option value="Abril">Abril</option>
                                    <option value="Mayo">Mayo</option>
                                    <option value="Junio">Junio</option>
                                    <option value="Julio">Julio</option>
                                    <option value="Agosto">Agosto</option>
                                    <option value="Septiembre">Septiembre</option>
                                    <option value="Octubre">Octubre</option>
                                    <option value="Noviembre">Noviembre</option>
                                    <option value="Diciembre">Diciembre</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md">
                                <div class="mb-3">
                                    <label for="paymentPrice">Monto</label>                                    
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="paymentPrice" name="paymentPrice">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md">
                                <label for="paymentExtraCkeck">Recargo</label>
                                <input class="form-check-input" type="checkbox" id="paymentExtraCkeck" name="paymentExtraCkeck" checked>                                
                                <div class="input-group mb-3">
                                    <span class="input-group-text">$</span>
                                    <input type="text" class="form-control" id="paymentExtra" name="paymentExtra">
                                </div>
                            </div>
                            <div class="col-md">
                                <div class="mb-3">
                                    <label for="paymentTotal">Total</label>                                    
                                    <div class="input-group">
                                        <span class="input-group-text">$</span>
                                        <input type="text" class="form-control" id="paymentTotal" name="paymentTotal" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row g-2 py-2">
                            <div class="col-md">
                                <label for="paymentMethod">Selecciona</label>                                
                                <select class="form-select" id="paymentMethod" name="paymentMethod" aria-label="Floating label select example">
                                    <option selected value="0">Método de pago</option>
                                    <option value="01">Efectivo</option>
                                    <option value="03">Transferencia bancaria</option>
                                    <option value="04">Tarjeta de crédito</option>
                                    <option value="28">Tarjeta de débito</option>
                                </select>
                            </div>
                            <div class="col-md">
                                <label for="paymentInvoice">Selecciona</label>                                
                                <select class="form-select" id="paymentInvoice" name="paymentInvoice" aria-label="Floating label select example">
                                    <option selected value=" ">Tipo de comprobante</option>
                                    <option value="0" data-id="0">Recibo simple</option>
                                    <option value="1" data-id="1">Factura</option>
                                </select>
                            </div>
                        </div>                        
                        <div class="row g-2 py-4">
                            <div class="col-md">
                                <button type="submit" class="btn btn-primary">Registrar pago</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!-- Boostrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js" integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>


<!-- datables -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.js"></script>

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/payments/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>

