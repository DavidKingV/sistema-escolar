<?php
require_once(__DIR__.'/../../backend/vendor/autoload.php');
require_once __DIR__ . '/../../backend/students/verify.php';

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;
use Vendor\Schoolarsystem\MicrosoftActions;

session_start();

$VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);

$dbConnection = new DBConnection();
$connection = $dbConnection->getConnection();

if (!$VerifySession['success']) {
    header('Location: ../index.php?sesion=expired');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/alumnos.css">
    <link href="https://cdn.datatables.net/v/bs5/dt-2.0.7/datatables.min.css" rel="stylesheet">    
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.rtl.min.css" />
    <title>Altas</title>
</head>
<body>

    <?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>
      
    <section class="home" id="home">           
        <div class="text">
            <p class="placeholder-glow">
            <span class="placeholder col-12" id="placeholder">Calificaciones del Alumno</span>
            </p></div>
        <hr class="border-top border-2 border-dark mx-auto w-25">

        <div class="row">

            <div class="col-lg-12">

                <!-- Overflow Hidden -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-title">Agregar calificación</h4>
                    </div>                   
                    <div class="card-body">
                        <div class="row g-2">
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
                                                    <select class="form-select subjectName" name="subject" id="subject" style="">
                                                        <option value="0">Selecciona una materia</option>
                                                    </select>  
                                                </div>
                                                <div class="col-md">
                                                    <select class="form-select subjectChildName" name="subjectChild" id="subjectChild" style="">
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
                                
                        </div>
                                <div class="row g-2 py-3">
                                    <div class="col-md">
                                        <button type="submit" class="btn btn-success">Agregar</button>
                                    </div>                                
                                </div>

                            </form>

                    </div>
                </div>    
                
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
                                        
                                    </tbody>    
                                </table>
                            </div>

                            <div class="tab-pane fade show" id="studentGroupDetails" role="tabpanel" aria-labelledby="studentGroupDetails" tabindex="0">
                                <div id="alertDisplay">
                                    <h4 class="card-title py-3">Agregar alumno al grupo</h4>
                                    <form id="studentGroupDetailsForm">
                                        <div class="row g-2">                                
                                            <div class="col-md">                                            
                                                <select class="form-select" id="studentIdGroup" name="studentIdGroup">                                                 
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
    </section>

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

<!-- Custom JS -->
<script type="module" src="../js/students/notes.js"></script>
<script type="module" src="../js/notes.js"></script>
<script src="../js/utils/validate.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>