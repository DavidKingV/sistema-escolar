import { initializeGroupsDataTable, initializeGroupsStudentsDataTable } from '../datatables/index.js';
import { FillTable, CleanInputsGroupsEdit, FillDivsGroups } from './forms.js';

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

$("#groupsTable").on("click", ".groupDetails", function(){
    let groupId = $(this).data("id");
    if (groupId) {
        window.location.href = 'grupos/detalles.php?id=' + groupId;
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'No se ha podido obtener el ID del grupo',
        });
    }
});

$("#groupsTable").on("click", ".groupSchedules", function(){
    let groupId = $(this).data("id");
    if (groupId) {
        window.location.href = 'grupos/horarios.php?id=' + groupId;
    }else{
        Swal.fire({
            icon: 'error',
            title: 'Oops...',
            text: 'No se ha podido obtener el ID del grupo',
        });
    }
});

$(function () {
    let currentPath = window.location.pathname;
    let specificPath = "/grupos/detalles.php";

    if (currentPath === specificPath) {
        //GetStudentsNames();
        const urlParams = new URLSearchParams(window.location.search);
        const groupId = urlParams.get('id');

        if (groupId) {
            GetDataGroupDetails(groupId);
            initializeGroupsStudentsDataTable(groupId);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'No se ha podido obtener el ID del grupo',
            });
        }
    }
});

const GetDataGroupDetails = async (groupId) => {
    try {
        const response = await $.ajax({
            url: '/backend/groups/routes.php',
            type: 'GET',
            data: {groupId: groupId, action: 'getGroupData'},
        });
        if (!response.success) {
            throw new Error(response.message);
        } else {
            FillDivsGroups(response);
        }
    } catch (error) {
        console.error('Error: ', error);
        Swal.fire({
            icon: 'error',
            title: 'Error al obtener los datos del grupo',
            text: error.message
        });
    }
}

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

$("#addStudentGroupForm").on("submit", function(e){
    e.preventDefault();
    let groupIdUrl = new URLSearchParams(window.location.search);
    let groupId = groupIdUrl.get('id');
    let studentId = $('#studentIdGroup').val();

    Swal.fire({
        title: '¿Estás seguro de agregar al alumno al grupo?', 
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            AddStudentGroup(groupId, studentId);
        }
    });
});

$("#groupStudentsTable").on("click", ".deleteGroupStudent", function(){    
    let studentId = $(this).data("id");

    Swal.fire({
        title: '¿Estás seguro de eliminar al alumno del grupo?', 
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            DeleteStudentGroup(studentId);
        }
    });
});

const DeleteStudentGroup = async (studentId) => {
    try {
        const response = await $.ajax({
            url: '/backend/groups/routes.php',
            type: 'POST',
            data: {studentId: studentId, action: 'deleteStudentGroup'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Alumno eliminado',
                text: response.message
            });
            // Reload the table
            $('#groupStudentsTable').DataTable().ajax.reload();
            GetStudentsNames();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar el alumno',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar el alumno',
            text: error.message
        });
    }
}

const AddStudentGroup = async (groupId, studentId) => {
    try {
        const response = await $.ajax({
            url: '/backend/groups/routes.php',
            type: 'POST',
            data: {groupId: groupId, studentId:studentId, action: 'addStudentGroup'},
            dataType: 'json'
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Alumno agregado',
                text: response.message
            });
            // Reload the table
            $('#groupStudentsTable').DataTable().ajax.reload();
            $('#addStudentGroup').validate().resetForm();
            $("#studentIdGroup").select2('val', 'All');
            GetStudentsNames();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar el alumno',
                text: response.message
            });
        }
    } catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar el alumno',
            text: error.message
        });
    }
};

const AddGroup = async (groupData) => {
    try {
        const response = await $.ajax({
            url: '/backend/groups/routes.php',
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
            url: '/backend/groups/routes.php',
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
            url: '/backend/groups/routes.php',
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

const GetStudentsNames = async () => {

    const GetStudentsSelect = async () => {
        try {
            const response = await $.ajax({
                url: '/backend/groups/routes.php',
                type: 'GET',
                data: {action: 'getStudentsNames'},
                dataType: 'json'
            });
            return response;
        } catch (error) {
            console.error('Error al obtener los datos:', error);
            throw new Error('Error al obtener los datos');
        }
    };

    try {
        const students = await GetStudentsSelect();

        if (!students || students.length === 0) {
            console.log('No se encontraron grupos');
            return;
        }

        let $select = $('#studentIdGroup');
        $.each(students, function(index, student) {
            if (student.success !== false) {
                let $option = $('<option>', {
                    value: student.id,
                    text: student.name
                });

                $select.append($option);
            }
        });

        $select.select2({
            theme: "bootstrap-5",
            placeholder: 'Selecciona uno o varios alumnos',
        });
    } catch (error) {
        console.error('Error al procesar los datos:', error.message);
    } 
   
};

const GetDataGroupEdit = async (groupId) => {
    try {
        // Función para obtener el valor predeterminado de la base de datos usando async/await
        const getDefaultCareer = async () => {
            const response = await $.ajax({
                url: '/backend/groups/routes.php',
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
                url: '/backend/groups/routes.php',
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
                        value: program.id,
                        text: '    ' + program.nombre                       
                    });
                    // Verificar si esta opción coincide con el valor predeterminado
                    if (program.nombre === defaultCareer) {
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
                url: '/backend/groups/routes.php',
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