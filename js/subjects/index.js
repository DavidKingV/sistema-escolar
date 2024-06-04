import { FillTable } from '../subjects/forms.js';
import { initializeSubjectsDataTable } from '../datatables/index.js';

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
            url: "../php/subjects/routes.php",
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
            url: "php/subjects/routes.php",
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
            url: "php/subjects/routes.php",
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
            url: "php/subjects/routes.php",
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