<?php include_once __DIR__ . '/../../backend/views/mainMenu.php'; ?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <!-- <link rel="stylesheet" href="assets/css/groups.css"> -->
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Duplicados</title>
</head>

<body>
    <div id="globalLoader"
        style="display: none; position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(33,37,41,0.5); z-index: 2000;">
        <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
            <div class="spinner-border text-light" role="status" style="width: 3rem; height: 3rem;">
                <span class="visually-hidden">Cargando...</span>
            </div>
        </div>
    </div>

    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Lista de duplicados</h2>
            </div>
            <!-- Card de Duplicados - agregar después del card de Lista de grupos -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items:center">
                    <span> Alumnos con grupos duplicados</span>
                    <button class="btn btn-sm btn-outline-warning" id="btnRefreshDuplicates">
                        <i class="bi bi-arrow-clockwise"></i> Actualizar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table" id="duplicatesTable">
                            <thead>
                                <tr>
                                    <th class="text-center">Alumno</th>
                                    <th class="text-center">Grupos de carrera</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Se llena dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Duplicados -->
            <div class="modal fade modal-lg" id="duplicatesModal" data-bs-backdrop="static" tabindex="-1"
                aria-labelledby="duplicatesModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="duplicatesModalLabel">
                                <i class="bi bi-exclamation-triangle text-warning"></i> Resolver duplicados
                            </h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <p class="text-muted mb-3">
                                Selecciona el grupo correcto para <strong id="duplicateStudentName"></strong>.
                                Los demás grupos de carrera serán eliminados.
                            </p>

                            <div id="duplicateGroupsList">
                                <!-- Se llena dinámicamente con cards por cada grupo -->
                            </div>

                            <div class="alert alert-warning mt-3" role="alert">
                                <i class="bi bi-info-circle"></i>
                                Esta acción eliminará al alumno de todos los grupos de carrera excepto el seleccionado.
                                Los cursos y diplomados no se verán afectados.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="button" class="btn btn-primary" id="btnSaveDuplicate" disabled>
                                <i class="bi bi-check-lg"></i> Confirmar grupo correcto
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>

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

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/groups/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>