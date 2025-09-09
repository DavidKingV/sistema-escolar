import { enviarPeticionAjax } from '../utils/ajax.js';
import { successAlert, errorAlert } from '../utils/alerts.js';

let phpPath = '/backend/carreers/routes.php';

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





$("#subjectsModal").on("hidden.bs.modal", function(){
    $('#subjectName').val('').trigger('change');
    $('#subjectName').select2('destroy');
    $('#subjectName').empty();

    $("#childSubjectDiv").prop("hidden", true);
    $("#childSubjectDiv").prop("disabled", true);
    $(".childSubjectName").empty();

    //$("#addSubjectCarreer")[0].reset();
    $("#addSubjectCarreer").validate().resetForm();
});

