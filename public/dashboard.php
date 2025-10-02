<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!--<link rel="stylesheet" href="assets/css/all.min.css">-->
    <link rel="stylesheet" href="assets/css/allMain.min.css">
    <!--<link rel="stylesheet" href="assets/css/dashboard.css">-->
    <title>Inicio</title>
</head>
<body>
<?php include_once __DIR__.'/../backend/views/mainMenu.php'; ?>
    <!-- Content -->
    <div id="content">
        <div class="container-fluid">
            <h2 class="mb-4">Dashboard</h2>
            
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-value">1,245</div>
                            <div class="stat-label">Estudiantes</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-value">58</div>
                            <div class="stat-label">Profesores</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-value">32</div>
                            <div class="stat-label">Clases</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="card-body">
                            <div class="stat-value">5</div>
                            <div class="stat-label">Eventos hoy</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            Calendario Escolar
                        </div>
                        <div class="card-body">
                            <div id="calendar"></div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            Próximos Eventos
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Reunión de padres</h6>
                                        <small class="text-muted">15 Marzo, 10:00 AM</small>
                                    </div>
                                    <span class="badge bg-primary rounded-pill">Importante</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Examen parcial</h6>
                                        <small class="text-muted">20 Marzo, 8:00 AM</small>
                                    </div>
                                    <span class="badge bg-warning rounded-pill">Examen</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Excursión</h6>
                                        <small class="text-muted">25 Marzo, 7:30 AM</small>
                                    </div>
                                    <span class="badge bg-success rounded-pill">Actividad</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="mb-1">Entrega de notas</h6>
                                        <small class="text-muted">30 Marzo, 3:00 PM</small>
                                    </div>
                                    <span class="badge bg-info rounded-pill">Académico</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!-- Boostrap -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js" integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>

<!-- globaljs -->
<script src="js/global/mainMenu.js"></script>

<!-- customs scripts -->
<script src="js/dashboard/index.js"></script>
<script type="module" src="js/utils/sessions.js"></script>

