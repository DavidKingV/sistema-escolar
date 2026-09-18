<?php
if (!isset($careerId)) {
    http_response_code(404);
    exit;
}
?>
<div id="careerSubjectsContent" data-career-id="<?= (int) $careerId ?>">
    <ul class="nav nav-tabs" id="careerSubjectsTabs" role="tablist">
        <li class="nav-item" role="presentation"><button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#add_subject_tab" type="button" role="tab">Registrar</button></li>
        <li class="nav-item" role="presentation"><button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#view_subjects_tab" type="button" role="tab">Ver Lista</button></li>
    </ul>
    <div class="tab-content" id="careerSubjectsTabContent">
        <div class="tab-pane fade show active py-4" id="add_subject_tab" role="tabpanel">
            <form id="addSubjectCareer">
                <input type="hidden" id="carreerId" name="careerId" value="<?= (int) $careerId ?>">
                <div class="col-md">
                    <div class="form-floating"><select class="form-select subjectName" name="subjectName" id="subjectName"><option selected value="0">Materia</option></select><label for="subjectName">Selecciona</label></div>
                    <label id="subjectName-error" class="error text-bg-danger" for="subjectName" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
                </div>
                <div class="col-md" id="childSubjectDiv">
                    <div class="form-floating"><select class="form-select childSubjectName" name="childSubjectName" id="childSubjectName" disabled><option selected value="">Submateria</option></select><label for="childSubjectName">Selecciona</label></div>
                    <label id="childSubjectName-error" class="error text-bg-danger" for="childSubjectName" style="font-size: 12px; border-radius: 10px; padding: 0 5px;"></label>
                </div>
                <div class="col-md"><button type="submit" class="btn btn-success">Agregar</button></div>
            </form>
        </div>
        <div class="tab-pane fade" id="view_subjects_tab" role="tabpanel">
            <table class="table table-striped table-hover" id="subjectsListTable">
                <thead><tr><th scope="col">Clave</th><th scope="col">Nombre</th></tr></thead>
                <tbody id="subjectsList"><tr><td colspan="2" class="text-center">No hay materias registradas</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
