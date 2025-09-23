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
    <!--<link rel="stylesheet" href="assets/css/dashboard.css">-->
    <title>Alta de Materias</title>
</head>
<body>

   <?php include __DIR__.'/../../backend/views/mainMenu.php'; ?>

    <div id="content">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0">Agregar Materias</h2>
            <a href="../materias.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver a la lista
            </a>
        </div>

        <div class="card border-primary shadow">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-book"></i> Información de la Materia
            </div>
                    <div class="card-body">
                        <form id="addSubjects">
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="subjectKey" class="form-label">Clave de la Materia</label>                                    
                                    <input type="text" class="form-control" id="subjectKey" name="subjectKey">
                                </div>   
                                <div class="col-md py-3">
                                    <label for="subjectName" class="form-label">Nombre de la Materia</label>                                    
                                    <input type="text" class="form-control" id="subjectName" name="subjectName">
                                </div>                                
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="subjectDes" class="form-label">Comentarios</label>                                    
                                    <textarea type="text" class="form-control" id="subjectDes" name="subjectDes"></textarea>
                                </div>                            
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>   

            </div>

            <!--<div class="col-lg-6">

                <div class="card mb-4">
                    <div class="card-header py-1">
                        <h6 class="m-0 font-weight-bold text-primary">Registrar profesor en materia</h6>
                    </div>
                    <div class="card-body">
                        <form id="addSubjects">
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="subjectNameTeacher" class="form-label">Nombre de la Materia</label>
                                    <label id="subjectNameTeacher-error" class="error text-bg-danger" for="subjectNameTeacher" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="subjectNameTeacher" name="subjectNameTeacher">
                                </div>                                
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <label for="teacherNameSubject" class="form-label">Nombre del profesor</label>
                                    <label id="teacherNameSubject-error" class="error text-bg-danger" for="teacherNameSubject" style="font-size: 12px; border-radius: 10px; padding: 0px 5px;"></label>
                                    <input type="text" class="form-control" id="teacherNameSubject" name="teacherNameSubject">
                                </div>                         
                            </div>
                            <div class="row g-2">
                                <div class="col-md py-3">
                                    <button type="submit" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>              

            </div>-->


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
<script type="module" src="../js/subjects/index.js"></script>
<script type="module" src="../js/utils/sessions.js"></script>