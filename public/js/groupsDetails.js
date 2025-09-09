import { initializeDataTable } from './global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from './global/alerts.js';
import { sendFetch } from './global/fetchCall.js';

const callback = '/public/api.php';

$(function() {
    getStudentsList($('#studentIdGroup'));
});


const getStudentsList = async (input) => {
    try {
        input.select2({
            theme: "bootstrap-5",
            placeholder: 'Selecciona al alumno',
            ajax: {
                url: callback,
                type: 'POST',
                dataType: 'json',
                delay: 250,
                data: function(params) {
                    return {
                        action: 'getNoGroupStudentsList',
                        search: params.term, // término de búsqueda
                        page: params.page || 1
                    };
                },
                processResults: function(data, params) {
                    params.page = params.page || 1;
                    
                    return {
                        results: data.results,
                        pagination: data.pagination
                    };
                },
                cache: true
            },
            minimumInputLength: 2,
            language: {
                inputTooShort: function() {
                    return "Por favor ingrese al menos 2 caracteres";
                },
                searching: function() {
                    return "Buscando...";
                },
                noResults: function() {
                    return "No se encontraron resultados.";
                }
            },
        });

        input.on('select2:select', function(e) {
            //alert('Seleccionado: ' + selectedData.text);
            //$('#patientId').val(e.params.data.id);
        });

    } catch (error) {
        console.error('Error al inicializar la búsqueda de alumnos:', error);
    }
};