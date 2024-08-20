import { enviarPeticionAjax } from '../utils/ajax.js';
import { successAlert, errorAlert } from '../utils/alerts.js';

let phpPath = 'php/carreers/routes.php';

const GetSubject = async (carreerId) => {
 
    const subject = async () => {
        try{
            const response = await $.ajax({
                url: phpPath,
                type: 'POST',
                data: {
                    carreerId : carreerId,
                    action: 'getSubject'
                }
            });
            return response;
        }catch(error){
            console.error(error);
        }
    }
    try{
        const  subjectsList = await subject();
        
        let $select = $('#subjectName');

        if (!subjectsList || subjectsList.length === 0) {
            errorAlert('No se encontraron materias');
            return;
        }

        if (subjectsList.success === false) {
            return;
        }

        $.each(subjectsList, function(index, subject) {
            if (subject.success !== false) {
                let $option = $('<option>', {
                    value: subject.subjectId,
                    text: subject.subjectName
                });

                $select.append($option);
                
            }else{
                let $option = $('<option>', {
                    value: '0',
                    text: 'No hay materias disponibles'
                });

                $select.append($option);
            }
        });

        $select.select2({
            dropdownParent: $("#subjectsModal"),
            theme: "bootstrap-5",
            placeholder: 'Selecciona la materia',
        });

    }catch(error){
        console.error(error);
    }

}   

const GetChildSubject = async (subjectId) => {
 
    const Childsubject = async () => {
        try{
            const response = await $.ajax({
                url: phpPath,
                type: 'POST',
                data: {
                    action: 'getChildSubject',
                    subjectId: subjectId
                }
            });
            return response;
        }catch(error){
            console.error(error);
        }
    }
    try{
        const  subjectsList = await Childsubject();

        if (!subjectsList || subjectsList.length === 0) {
            errorAlert('No se encontraron materias');
            return;
        }
        if (subjectsList.success === false) {
            return;
        }

        let $select = $('.childSubjectName');

        $.each(subjectsList, function(index, subject) {
            if (subject.success !== false) {
                let $option = $('<option>', {
                    value: subject.childSubjectId,
                    text: subject.childSubjectName
                });

                $select.append($option);
                
            }
        });

        $select.select2({
            dropdownParent: $("#subjectsModal"),
            theme: "bootstrap-5",
            placeholder: 'Selecciona la submateria',
        });
        
        $("#childSubjectDiv").prop("hidden", false);
        $("#childSubjectName").prop("disabled", false);
    }catch(error){
        console.error(error);
    }

} 

const AddSubjectsCarreer = async (subjectAddData) => {
    try{
        enviarPeticionAjax(phpPath, 'POST', {subjectAddData: subjectAddData, action : 'addSubjectsCarreer'})
            .done(function(response){
                if(response.success){
                    successAlert(response.message);
                }else{
                    errorAlert(response.message);
                }
            })
    }catch{
        errorAlert("No se pudo agregar la materia a la carrera");
    }
}   

$("#carreersTable").on("click", ".subjectsCarreer", function(){
    $("#carreerId").val($(this).data("id"));
    GetSubject($(this).data("id"));
});

$("#subjectName").on("change", function(){
    GetChildSubject( $(this).val() );
});

$("#subjectsModal").on("hidden.bs.modal", function(){
    $('#subjectName').val('').trigge('change');
    $('#subjectName').select2('destroy');
    $('#subjectName').empty();

    $("#childSubjectDiv").prop("hidden", true);
    $("#childSubjectDiv").prop("disabled", true);
    $(".childSubjectName").empty();

    $("#addSubjectCarreer")[0].reset();
    $("#addSubjectCarreer").validate().resetForm();
});

$("#addSubjectCarreer").on("submit", function(e){
    e.preventDefault();

    const subjectAddData = $(this).serialize();

    Swal.fire({
        title: '¿Estás seguro de agregar esta materia a la carrera?', 
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddSubjectsCarreer(subjectAddData);
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