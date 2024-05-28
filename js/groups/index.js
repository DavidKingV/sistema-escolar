import { initializeGroupsDataTable } from '../datatables/index.js';
import { FillTable, CleanInputsGroupsEdit } from './forms.js';

initializeGroupsDataTable();

$("#groupsTable").on("click", ".editGroup", function() {
    let groupId = $(this).data("id");
    if (groupId) {
        GetDataGroupEdit(groupId);
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'No se ha podido obtener el ID del grupo',
        });
    }
});

$("#updateGroup").on("submit", function(e){
    e.preventDefault();
    let groupDataEdit = $(this).serialize();

    Swal.fire({
        title: '¿Estás seguro de actualizar al grupo?', 
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            UpdateGroup(groupDataEdit);
        }
    });
});

$("#groupsTable").on("click", ".deleteGroup", function(){
    let groupId = $(this).data("id");
    if (groupId) {
        Swal.fire({
            title: '¿Estás seguro de eliminar al grupo?', 
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: 'rgb(48, 133, 214)',
            cancelButtonColor: 'rgb(221, 51, 51);',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if(result.isConfirmed){
                DeleteGroup(groupId);
            }
        });
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'No se ha podido obtener el ID del grupo',
        });
    }
});

$("#addGroups").on("submit", function(e){
    e.preventDefault();
    let groupData = $(this).serialize();

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
});

const AddGroup = async (groupData) => {
    try {
        const response = await $.ajax({
            url: '../php/groups/routes.php',
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

const DeleteGroup = async (groupId) => {
    try {
        const response = await $.ajax({
            url: 'php/groups/routes.php',
            type: 'POST',
            data: {groupId: groupId, action: 'deleteGroup'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Grupo eliminado',
                text: response.message
            });
            // Reload the table
            $('#groupsTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar el grupo',
                text: response.message
            });
        }
    } catch (error) {
        console.error('Error: ', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el grupo',
            text: error.message
        });
    }
}

const UpdateGroup = async (groupDataEdit) => {
    try {
        const response = await $.ajax({
            url: 'php/groups/routes.php',
            type: 'POST',
            data: {groupDataEdit: groupDataEdit, action: 'updateGroup'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Grupo actualizado',
                text: response.message
            });
            // Reload the table
            $('#groupsTable').DataTable().ajax.reload();
            $('#GroupsEditModal').modal('hide');
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar el grupo',
                text: response.message
            });
        }        
    } catch (error) {
        console.error('Error: ', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar el grupo',
            text: error.message
        });
    }
};

const GetDataGroupEdit = async (groupId) => {
    try {
        // Función para obtener el valor predeterminado de la base de datos usando async/await
        const getDefaultCareer = async () => {
            const response = await $.ajax({
                url: 'php/groups/routes.php',
                type: 'GET',
                data: {groupId: groupId, action: 'getGroupData'},
            });
            if (!response.success) {
                throw new Error(response.message);
            } else {
                FillTable(response);
                return response.carreer_name;               
            }
        };

        // Función para cargar el JSON de carreras
        const loadCareers = async () => {
            const response = await $.ajax({
                url: 'php/groups/routes.php',
                type: 'GET',
                data: {action: 'getGroupsJson'}
            });
            if (!response) {
                throw new Error(response.message);
            } else {
                return response;
            }
            
        };

        // Obtener el valor predeterminado
        const defaultCareer = await getDefaultCareer();

        // Cargar el JSON de carreras
        const careers = await loadCareers();

        let $selectEdit = $('#carreerNameGroupEdit');
        $.each(careers, function(area, subareas) {
            let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });

            $.each(subareas, function(subarea, programs) {
                let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                $.each(programs, function(index, program) {
                    let $option = $('<option>', {
                        value: program,
                        text: '    ' + program                       
                    });

                    // Verificar si esta opción coincide con el valor predeterminado
                    if (program === defaultCareer) {
                        $option.prop('selected', true); // Establecer la opción como seleccionada
                    }

                    $subOptgroup.append($option); // Agrega la opción al subgrupo
                });
                $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
            });

            $selectEdit.append($mainOptgroup);
        });

        // Inicializar Select2
        $selectEdit.select2({
            theme: "bootstrap-5",
            dropdownParent: $('#GroupsEditModal')
        });

    } catch (error) {
        console.error('Error: ', error);
    }
};

const GetCarreerName = async () => {
    try {
        // Función para obtener el valor predeterminado de la base de datos usando async/await

        // Función para cargar el JSON de carreras
        const loadCareers = async () => {
            const response = await $.ajax({
                url: '../php/groups/routes.php',
                type: 'GET',
                data: {action: 'getGroupsJson'}
            });
            if (!response) {
                throw new Error(response.message);
            } else {
                return response;
            }
            
        };

        // Cargar el JSON de carreras
        const careers = await loadCareers();

        let $select = $('#carreerNameGroup');
        $.each(careers, function(area, subareas) {
            let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });

            $.each(subareas, function(subarea, programs) {
                let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                $.each(programs, function(index, program) {
                    let $option = $('<option>', {
                        value: program.id,
                        text: '    ' + program.nombre                       
                    });

                    $subOptgroup.append($option); // Agrega la opción al subgrupo
                });
                $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
            });

            $select.append($mainOptgroup);
        });

        // Inicializar Select2
        $select.select2({
            theme: "bootstrap-5"
        });

    } catch (error) {
        console.error('Error: ', error);
    }
};

GetCarreerName();

//miselanious

$("#GroupsEditModal").on("hidden.bs.modal", function(){
    CleanInputsGroupsEdit();
});