<?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/allMain.min.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Altas</title>
</head>
<body>

      
    <div id="content">
        <div class="container-fluid">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="mb-0">Agregar Calificaciones</h2>
                <a href="../alumnos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a la lista de alumnos
                </a>
            </div>

            <!-- Student Info Card -->
            <div class="card border-primary shadow mb-4">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user-graduate"></i> Información de la materia
                </div>
                <div class="card-body">
                    <form id="addGradeStudent">
                        <table class="table table-striped" id="addGradesTable">
                            <thead>
                                <tr>
                                    <th scope="col">Materia</th>
                                    <th scope="col">Calificación Continua</th>
                                    <th scope="col">Calificación de Examen</th>
                                    <th scope="col">Calificación Final</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th scope="row">
                                        <div class="row g-2">
                                            <div class="col-md">
                                                <select class="form-select subjectName" name="subject" id="subject">
                                                    <option value="0">Selecciona una materia</option>
                                                </select>
                                            </div>
                                            <div class="col-md">
                                                <select class="form-select subjectChildName" name="subjectChild" id="subjectChild">
                                                    <option value="0">Selecciona una submateria</option>
                                                </select>
                                            </div>
                                        </div>
                                    </th>
                                    <td><input type="text" name="gradeCont" id="gradeCont" class="form-control"></td>
                                    <td><input type="text" name="gradetest" id="gradetest" class="form-control"></td>
                                    <td><input type="text" name="gradefinal" id="gradefinal" class="form-control"></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row g-2 py-3">
                            <div class="col-md">
                                <button type="submit" class="btn btn-success">Agregar</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabs Card -->
            <div class="card mb-4">
                <div class="card-header">
                    <ul class="nav nav-tabs card-header-tabs">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="studentsListGrades" data-bs-toggle="tab" data-bs-target="#studentGradesTable" type="button" role="tab" aria-controls="studentGradesTable" aria-selected="true">Calificaciones</button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link" id="studentDetails" data-bs-toggle="tab" data-bs-target="#studentGroupDetails" type="button" role="tab" aria-controls="studentGroupDetails" aria-selected="true">Grupo</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content" id="myTabContent">
                        <!-- Calificaciones Tab -->
                        <div class="tab-pane fade show active" id="studentGradesTable" role="tabpanel" aria-labelledby="studentGradesTable" tabindex="0">
                            <h4 class="card-title py-3">Lista de calificaciones</h4>
                            <table class="table table-secondary table-striped" id="gradesStudentTable">
                                <thead>
                                    <tr>
                                        <th class="text-center">ID</th>
                                        <th class="text-center">Materia</th>
                                        <th class="text-center">C. Continua</th>
                                        <th class="text-center">C. de Examen</th>
                                        <th class="text-center">C. Final</th>
                                        <th class="text-center">Extraordinario</th>
                                        <th class="text-center">Ult. Actualización</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data rows go here -->
                                </tbody>
                            </table>
                        </div>
                        <!-- Grupo Tab -->
                        <div class="tab-pane fade" id="studentGroupDetails" role="tabpanel" aria-labelledby="studentGroupDetails" tabindex="0">
                            <div id="alertDisplay">
                                <h4 class="card-title py-3">Agregar alumno al grupo</h4>
                                <form id="studentGroupDetailsForm">
                                    <div class="row g-2">
                                        <div class="col-md">
                                            <select class="form-select" id="studentIdGroup" name="studentIdGroup">
                                                <!-- Options go here -->
                                            </select>
                                            <div>
                                                <p class="py-1">
                                                    <label id="studentIdGroup-error" class="error text-bg-danger" for="studentIdGroup" style="font-size: 12px; border-radius: 10px; padding: 0px 5px; display:none;"></label>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md py-1">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Agregar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>

<!-- Modales-->

<div class="modal fade" id="makeOverExamModal" aria-labelledby="makeOverExamModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="makeOverExamModalLabel"></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="makeOverExamModalBody">
            
        </div>
        </div>
    </div>
</div>

<div class="modal fade" id="makeOverViewModal" aria-labelledby="makeOverViewModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
        <div class="modal-header">
            <h1 class="modal-title fs-5" id="makeOverViewModalLabel"></h1>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" id="makeOverViewModalBody">
            
        </div>
        </div>
    </div>
</div>


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
<script src="../js/global/mainMenu.js"></script>

<!-- Custom JS -->
<script type="module" src="../js/students/notes.js"></script>
<script type="module" src="../js/notes.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>