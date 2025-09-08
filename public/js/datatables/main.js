import { errorAlert } from '../utils/alerts.js';

export function initializeDataTable(element, url, data, columns) {
    $(element).DataTable({
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.4/i18n/es-ES.json',
        },
        ordering: false,
        paging: true,
        processing: true,
        ajax: {
            url: url, 
            type: "POST",
            data: data,
            dataSrc: function(data){
                if(!data[0].success){
                    errorAlert(data[0].message);
                    return [];
                }
                return data;
            }
        },
        "columns": columns
    });
}