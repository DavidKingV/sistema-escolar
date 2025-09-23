<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <link rel="stylesheet" href="../assets/css/groups.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Horarios</title>
</head>
<body>

    <?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
      
    <div id="content">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Horarios/Clases programadas</h2>
                <a href="../grupos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la lista de grupos
                </a>
            </div>

            <div class="row">
                <div class="col-lg-12 mb-4">
                    <div class="card border-primary shadow">
                        <div class="card-header bg-primary text-white">
                            <i class="fas fa-user-graduate"></i> Horarios
                        </div>
                        <div class="card-body">
                            <form id="formAddSchedule" class="row g-3">
                                <div class="col-md-4" hidden>
                                    <label for="groupId" class="form-label">Grupo: </label>
                                    <input type="text" class="form-control" id="groupId" name="groupId" value="<?php echo $_GET['id'] ?? NULL; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="title" class="form-label">Titulo: </label>
                                    <input type="text" class="form-control" id="title" name="title" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="date" class="form-label">Fecha: </label>
                                    <input type="date" class="form-control" id="date" name="date" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputStart" class="form-label">Hora de inicio: </label>
                                    <input type="time" class="form-control" id="inputStart" name="inputStart" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="inputEnd" class="form-label">Hora de fin: </label>
                                    <input type="time" class="form-control" id="inputEnd" name="inputEnd" required>
                                </div>
                                <div class="col-md-12">
                                    <label for="description" class="form-label">Descripción: </label>
                                    <textarea class="form-control" id="description" name="description" required rows="5"></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-12">
                    <div class="card mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Lista de eventos para el grupo</h6>
                        </div>
                        <div class="card-body">
                            <table id="schedulesTable" class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>Titulo</th>
                                        <th>Fecha</th>
                                        <th>Hora de inicio</th>
                                        <th>Hora de fin</th>
                                        <th>Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
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
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.js"></script>

<!-- datables -->
<script src="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.js"></script>

<!-- select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- globaljs -->
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/groupsSchedules.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>