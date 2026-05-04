import { errorAlert } from './alerts.js';

export function initializeDataTable(element, url, data, columns) {
    $(element).DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
            emptyTable: "No hay registros disponibles.",
            zeroRecords: "No se encontraron resultados."
        },
        ordering: false,
        paging: true,
        processing: true,
        destroy: true,
        ajax: {
            url: url, 
            type: "POST",
            data: data,            
            dataSrc: function(data){
                if(!data.success){
                    //errorAlert(data.message);
                    return [];
                }
                // Si viene success pero data vacío
                if (!data.data || data.data.length === 0) {
                    // Puedes mostrar un alert opcional si lo prefieres
                    // errorAlert("No hay datos para mostrar.");
                    return [];
                }

                return data.data;
            }
        },
        "columns": columns
    });
}