import { initializeCarreersDataTable } from '../datatables/index.js';
import { FillTable, ClearInputsEditTeachers } from '../carreers/forms.js';

initializeCarreersDataTable();

$(function() {

    let currentPath = window.location.pathname;
    console.log("Current Path:", currentPath);

    if (currentPath.endsWith("/carreras/altas.php")) {
        console.log("Cargando JSON de carreras...");

        $.getJSON('../../backend/carreers/areas.json', function(carreras) {
            let $select = $('#careerName');
            $.each(carreras, function(area, subareas) {
                let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });
        
                $.each(subareas, function(subarea, programas) {
                    let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                    $.each(programas, function(index, programa) {
                        $subOptgroup.append($('<option>', {
                            text: '    ' + programa,
                            value: programa,
                            'data-area': area.replace(/_/g, ' '), // Guarda el área en un atributo de datos
                            'data-subarea': subarea.replace(/_/g, ' ') // Guarda el subárea en un atributo de datos
                        })); // Agrega espacios para simular jerarquía
                    });
                    $mainOptgroup.append($subOptgroup.children()); // Añade opciones del subgrupo al grupo principal
                });
        
                $select.append($mainOptgroup);
            });
        
            $select.select2({
                theme: "bootstrap-5",
            });
        
            // Controlador de eventos change para actualizar los campos de entrada
            $select.on('change', function() {
                let selectedOption = $(this).find('option:selected');
                $('#careerArea').val(selectedOption.data('area'));
                $('#careerSubarea').val(selectedOption.data('subarea'));
            });
        });
    }
   
});

$("#carreersTable").on("click", ".editCarreer", function(e) {
    e.preventDefault();
    let idCarreer = $(this).data("id");

    if(idCarreer){
        GetCarreerData(idCarreer);
    }else {
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para editar.'
        });
    }
});


$("#addCareers").on( "submit", function( event ) {
    event.preventDefault();
    const carreerData = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de agregar la carrera?', 
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, agregar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            if($(this).valid()){
                AddCareer(carreerData);
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

$("#updateCareer").on("submit", function(event) {
    event.preventDefault();
    let carreerDataEdit = $(this).serialize();
    Swal.fire({
        title: '¿Estás seguro de actualizar la carrera?', 
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, actualizar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            UpdateCarreer(carreerDataEdit);
        }
    });
});

$("#carreersTable").on("click", ".deleteCarreer", function() {
    let idCarreer = $(this).data("id");
    Swal.fire({
        title: '¿Estás seguro de eliminar la carrera?', 
        text: 'Esta acción no se puede deshacer',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: 'rgb(48, 133, 214)',
        cancelButtonColor: 'rgb(221, 51, 51);',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if(result.isConfirmed){
            DeleteCarreer(idCarreer);
        }
    });
});

const DeleteCarreer = async (idCarreer) => {
    try {
        const response = await $.ajax({
            url: '../backend/carreers/routes.php',
            type: 'POST',
            data: {idCarreer: idCarreer, action: 'deleteCarreer'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Carrera eliminada',
                text: response.message
            });
            // Reload the table
            $('#carreersTable').DataTable().ajax.reload();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al eliminar la carrera',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al eliminar la carrera',
            text: 'Ocurrió un error al eliminar la carrera, por favor intenta de nuevo más tarde.'
        });
    }
}

const UpdateCarreer = async (carreerDataEdit) => {
    try {
        const response = await $.ajax({
            url: '../backend/carreers/routes.php',
            type: 'POST',
            data: {carreerDataEdit: carreerDataEdit, action: 'updateCarreer'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Carrera actualizada',
                text: response.message
            });
            // Reload the table
            $('#carreersTable').DataTable().ajax.reload();
            $('#CareerEditModal').modal('hide');
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al actualizar la carrera',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al actualizar la carrera',
            text: 'Ocurrió un error al actualizar la carrera, por favor intenta de nuevo más tarde.'
        });
    }
}


const GetCarreerData = async (idCarreer) => {
    try {
        // Función para obtener el valor predeterminado de la base de datos usando async/await
        const getDefaultCareer = async () => {
            const response = await $.ajax({
                url: '../backend/carreers/routes.php', // Cambia esta URL a tu ruta real
                type: 'GET',
                data: { idCarreer: idCarreer, action: 'getCareerData' }
            });
            if (!response.success) {
                throw new Error(response.message);
            } else {
                FillTable(response);
                return response.name;
            }
        };

        // Función para cargar el JSON de carreras
        const loadCareers = async () => {
            const response = await $.getJSON('../backend/carreers/areas.json');
            return response;
        };

        // Obtener el valor predeterminado
        const defaultCareer = await getDefaultCareer();

        // Cargar el JSON de carreras
        const careers = await loadCareers();

        let $selectEdit = $('#careerNameEdit');
        $.each(careers, function(area, subareas) {
            let $mainOptgroup = $('<optgroup>', { label: area.replace(/_/g, ' ') });

            $.each(subareas, function(subarea, programs) {
                let $subOptgroup = $('<optgroup>', { label: '  ' + subarea.replace(/_/g, ' ') }); // Agrega espacios para simular jerarquía
                $.each(programs, function(index, program) {
                    let $option = $('<option>', {
                        value: program,
                        text: '    ' + program,
                        'data-area': area.replace(/_/g, ' '), // Guarda el área en un atributo de datos
                        'data-subarea': subarea.replace(/_/g, ' ') // Guarda el subárea en un atributo de datos
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
            dropdownParent: $('#CareerEditModal'),
            bindEvents: false,
        });

        const updateInputFields = () => {
            let selectedOption = $selectEdit.find('option:selected');
            $('#carreerAreaEdit').val(selectedOption.data('area'));
            $('#careerSubareaEdit').val(selectedOption.data('subarea'));
        };

        // Llamar a la función para actualizar los campos de entrada inicialmente
        updateInputFields();

        $selectEdit.on('change', function() {
            let selectedOption = $(this).find('option:selected');
            $('#carreerAreaEdit').val(selectedOption.data('area'));
            $('#careerSubareaEdit').val(selectedOption.data('subarea'));
        });

    } catch (error) {
        console.error('Error: ', error);
    }
};

const AddCareer = async (carreerData) => {
    try {
        const response = await $.ajax({
            url: '../../backend/carreers/routes.php',
            type: 'POST',
            data: {carreerData: carreerData, action: 'addCareer'}
        });
        if(response.success){
            // Show a success message
            Swal.fire({
                icon: 'success',
                title: 'Carrera agregada',
                text: response.message
            });
            // Reload the table
            $('#addCareers')[0].reset();
        }else{
            // Show an error message
            Swal.fire({
                icon: 'error',
                title: 'Error al agregar la carrera',
                text: response.message
            });
        }
    }
    catch (error) {
        Swal.fire({
            icon: 'error',
            title: 'Error al agregar la carrera',
            text: 'Ocurrió un error al agregar la carrera, por favor intenta de nuevo más tarde.'
        });
    }
}

//miselanius

$("#CareerEditModal").on("hidden.bs.modal", function() {
    ClearInputsEditTeachers();
});