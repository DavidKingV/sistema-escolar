import { FillTable, FillChildInfo, ClearSubjectChildInputs, onChangeInputs } from '../subjects/forms.js';
import { initializeSubjectsDataTable } from '../datatables/index.js';
import { observeDOMChanges } from '../utils/mutationObserver.js';

initializeSubjectsDataTable();

$("#subjectsTable").on('click', '.editSubject', function() {
    let subjectId = $(this).data('id');
    
    if (subjectId) {
        GetSubjectData(subjectId);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID de lamateria, por favor intenta de nuevo.'
        });
    }
});

$("#updateSubjectChild").click(function() {

    let subjectChildData = $("#subjectChildInfo").serialize();

    Swal.fire({
        title: '¿Estás seguro de actualizar los datos de la materia hija?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateSubjectChild(subjectChildData);
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });

});

$("#deleteSubjectChild").click(function() {

    let subjectChildId = $("#idChildSubjectInfo").val();

    Swal.fire({
        title: '¿Estás seguro de eliminar la materia hija?',
        text: 'No podrás revertir esta acción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            DeleteSubjectChild(subjectChildId);
        }
    });

});

$("#subjectsTable").on('click', '.subjectChildInfo', async function() {

    observeDOMChanges('subjectChildInfo', 'readonly');
    observeDOMChanges('idMainSubjectInfo', 'readonly');

    let subjectFatherId = $(this).data('idfather');
    let subjectChildId = $(this).data('idchild');
    if (subjectFatherId && subjectChildId) {
        await GetChildSubjectsData(subjectFatherId, subjectChildId);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID de la materia, por favor intenta de nuevo.'
        });
    }

});


$("#subjectsTable").on('click', '.addChildSubject', function() {

    observeDOMChanges('idMainSubject', 'readonly');
    observeDOMChanges('subjectManinName', 'readonly');
    observeDOMChanges('carrerId', 'readonly');

    let subjectId = $(this).data('id');
    let subjectName = $(this).data('name');
    let carrerId = $(this).data('carrerid');

    if (subjectId) {
        $("#idMainSubject").val(subjectId); 
        $("#subjectManinName").val(subjectName);  
        $("#carrerId").val(carrerId); 
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'No se pudo obtener el ID de la materia, por favor intenta de nuevo.'
        });
    
    }
});

$("#addSubjectChild").submit(function(e) {
    e.preventDefault();
    let subjectChildData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar la materia hija?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddSubjectChild(subjectChildData);
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });
});

$("#updateSubject").submit(function(e) {
    e.preventDefault();
    let subjectDataEdit = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de actualizar los datos de la materia?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                UpdateSubjectData(subjectDataEdit);
                $('#SubjectsEditModal').modal('hide');
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });
});

$("#subjectsTable").on('click', '.deleteSubject', function() {
    let subjectId = $(this).data('id');
    Swal.fire({
        title: '¿Estás seguro de eliminar la materia?',
        text: 'No podrás revertir esta acción.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            DeleteSubject(subjectId);
        }
    });
});

$("#addSubjects").submit(function(e) {
    e.preventDefault();
    let subjectData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar la materia?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddSubject(subjectData);
            }else{
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la validación',
                    text: 'Por favor, verifica que todos los campos estén llenos y sean correctos.'
                });
            }
        }
    });
});

const AddSubject = async (subjectData) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "POST",
            data: {subjectData: subjectData, action: "addSubject"}
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Materia agregada',
                text: response.message
            }).then(() => {
                $('#addSubjects')[0].reset();
                $('#subjectsTable').DataTable().ajax.reload();
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar la materia',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar la materia',
            text: 'Ocurrió un error al agregar la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

const DeleteSubject = async (subjectId) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "POST",
            data: {subjectId: subjectId, action: "deleteSubject"}
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Materia eliminada',
                text: response.message
            }).then(() => {
                $('#subjectsTable').DataTable().ajax.reload();
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar la materia',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar la materia',
            text: 'Ocurrió un error al eliminar la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

const UpdateSubjectData = async (subjectDataEdit) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "POST",
            data: {subjectDataEdit: subjectDataEdit, action: "updateSubjectData"}
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Datos actualizados',
                text: response.message
            }).then(() => {
                $('#subjectsTable').DataTable().ajax.reload();
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar los datos',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar los datos de la materia',
            text: 'Ocurrió un error al actualizar los datos de la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

const GetSubjectData =  async (subjectId) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "GET",
            data: {subjectId: subjectId, action: "getSubjectData"},
        });
        if(response.success){
            FillTable(response);
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al obtener los datos',
                text: response.message,
                confirmButtonText: 'Iniciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'index.html';
                }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos de la materia',
            text: 'Ocurrió un error al obtener los datos de la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

const UpdateSubjectChild = async (subjectUpdateChildData) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "POST",
            data: {subjectUpdateChildData: subjectUpdateChildData, action: "updateSubjectChild"}
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Datos actualizados',
                text: response.message
            }).then(() => {
                $('#subjectsTable').DataTable().ajax.reload();
                $('#childSubjectsModal').modal('hide');
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar los datos',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar los datos de la materia',
            text: 'Ocurrió un error al actualizar los datos de la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

const AddSubjectChild = async (subjectChildData) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "POST",
            data: {subjectChildData: subjectChildData, action: "addSubjectChild"}
        });
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Materia hija agregada',
                text: response.message
            }).then(() => {
                $('#SubjectsChildAddModal').modal('hide');
                $('#addSubjectChild')[0].reset();
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar la materia hija',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar la materia hija',
            text: 'Ocurrió un error al agregar la materia hija, por favor intenta de nuevo más tarde.'
        });
    }
}

const GetChildSubjectsData  = async (subjectFatherId, subjectChildId) => {
    try {
        const response = await $.ajax({
            url: "/backend/subjects/routes.php",
            type: "GET",
            data: {subjectFatherId: subjectFatherId, subjectChildId: subjectChildId, action: "getChildSubjectsData"},
        });
        if(response.success){
            FillChildInfo(response);
            onChangeInputs();
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al obtener los datos',
                text: response.message,
                confirmButtonText: 'Iniciar sesión'
            }).then((result) => {
                if(result.isConfirmed){
                    window.location.href = 'index.html';
                }
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos de la materia',
            text: 'Ocurrió un error al obtener los datos de la materia, por favor intenta de nuevo más tarde.'
        });
    }
}

//miselanious

$("#SubjectsChildAddModal").on('hidden.bs.modal', function() {
    ClearSubjectChildInputs();
});