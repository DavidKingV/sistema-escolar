import { initializeGroupsDataTable, initializeGroupsStudentsDataTable } from '../datatables/index.js';
import { FillTable, CleanInputsGroupsEdit, FillDivsGroups } from './forms.js';
import { validateForm, capitalizeAllWords, capitalizeAll } from '../global/validate/index.js';
import { errorAlert, successAlert, infoAlert, loadingSpinner, confirmAlert } from '../utils/alerts.js';

$("#addGroups").on("submit", function(e){
    e.preventDefault();
    let groupData = $(this).serialize();

    if($(this).valid()){
        Swal.fire({
            title: '¿Estás seguro de agregar al grupo?', 
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, agregar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                AddGroup(groupData);
            }
        });
    }else{
        infoAlert('Por favor completa el formulario correctamente');
    }
});

$("#keyGroup, #nameGroup").on("input", function(){
    let formattedValue = capitalizeAll($(this).val());
    $(this).val(formattedValue);
});


const AddGroup = async (groupData) => {
    try {
        const response = await $.ajax({
            url: '../../backend/groups/routes.php',
            type: 'POST',
            data: {groupData: groupData, action: 'addGroup'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Grupo agregado',
                text: response.message
            });
            // Reload the table
            $('#groupsTable').DataTable().ajax.reload();
            $('#addGroups')[0].reset();
            $('#addGroups').validate().resetForm();
            $('#carreerNameGroup').val('Carrera').trigger('change') ;
        }
    }catch (error) {
        console.error('Error: ', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el grupo',
            text: error.message
        });
    }
}
$(function () {
    validateForm("#addGroups", {
        carreerNameGroup: {
            required: true,
            valueNotEquals: "0"
        },
        keyGroup: {
            required: true,
            minlength: 3,
            maxlength: 10,
        },
        nameGroup: {
            required: true,
            minlength: 3,
            maxlength: 50
        },
        startDate: {
            required: true,
            dateISO: true
        },
        endDate: {
            required: true,
            dateISO: true
        },
        descriptionGroup: {
            required: true,
            minlength: 3,
            maxlength: 255
        }
    },
    {
        carreerNameGroup: {
            required: "Por favor selecciona una carrera",
            valueNotEquals: "Por favor, selecciona una opción"
        },
        keyGroup: {
            required: "Por favor ingresa la clave del grupo",
            minlength: "La clave del grupo debe tener al menos 3 caracteres",
            maxlength: "La clave del grupo no debe exceder los 10 caracteres"
        },
        nameGroup: {
            required: "Por favor ingresa el nombre del grupo",
            minlength: "El nombre del grupo debe tener al menos 3 caracteres",
            maxlength: "El nombre del grupo no debe exceder los 50 caracteres"
        },
        startDate: {
            required: "Por favor ingresa la fecha de inicio",
            dateISO: "Por favor ingresa una fecha válida (YYYY-MM-DD)"
        },
        endDate: {
            required: "Por favor ingresa la fecha de fin",
            dateISO: "Por favor ingresa una fecha válida (YYYY-MM-DD)"
        },
        descriptionGroup: {
            required: "Por favor ingresa una descripción",
            minlength: "La descripción debe tener al menos 3 caracteres",
            maxlength: "La descripción no debe exceder los 255 caracteres"
        }
    });
});
