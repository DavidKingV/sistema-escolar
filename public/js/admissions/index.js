import { initializeDataTable } from '../global/dataTables.js';
import { confirmAlert, successAlertAuto, errorAlert, loadingSpinner } from '../global/alerts.js';
import { sendFetch } from '../global/fetchCall.js';
import { capitalizeAll } from '../global/validate/index.js';

const callback = '../../backend/admissions/routes.php';

$(function() {
    initializeDataTable('#newAdmissionsTable', callback, { action: 'getAllNewAdmissions' }, [
        {
            "data": "actions",
            "render": function(data, type, row) {
                if (!data) return "";
                return `<button class="btn btn-success btn-circle approveStudent" data-id="${row.id}">
                            <i class="bi bi-check2-circle"></i>
                        </button>
                        <br>
                        <button class="btn btn-danger btn-circle deleteApplication" data-id="${row.id}">
                            <i class="bi bi-trash-fill"></i>
                        </button>`;
            },
            "className": "text-center"
        },      
        { 
            "data": "actions",
            "render": function(data, type, row) {
                if (!data) return "";
                return `<input type="text" class="form-control form-control-sm editable-field" name="controlNo" value="${row.controlNo || ''}">`;
            },
            "className": "text-center"
        },
        { 
            "data": "firstName", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "lastNames", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
        },
        { 
            "data": "gender", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
            "visible": false   // ⬅️ OCULTA LA COLUMNA COMPLETAMENTE
        },
        { 
            "data": "birthday", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "placeBirth", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
            "visible": false   // ⬅️ OCULTA LA COLUMNA COMPLETAMENTE
        },
        { 
            "data": "nationality", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "curp", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "age", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "civilStatus", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
            "visible": false   // ⬅️ OCULTA LA COLUMNA COMPLETAMENTE
        },
        { 
            "data": "adress", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
            "visible": false   // ⬅️ OCULTA LA COLUMNA COMPLETAMENTE
        },
        { 
            "data": "phone", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "email", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        { 
            "data": "lastStudies", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center",
            "visible": false   // ⬅️ OCULTA LA COLUMNA COMPLETAMENTE
        },
        { 
            "data": "program", 
            "render": function (data) {
                return data || '';
            },
            "className": "text-center"
        },
        {
            "data": "pdf",
            "render": function (data, type, row) {
                if (data) {
                    return `
                        <button type="button" class="btn btn-outline-primary btn-sm ver-pdf" data-pdf="${data}">
                            Ver PDF
                        </button>`;
                } else {
                    return `<span class="text-muted">Sin archivo</span>`;
                }
            },
            "className": "text-center"
        }  
    ]);

});

function abrirYDescargarPDFBase64(base64Data, nombreArchivo = 'documento.pdf') {
    // Decodificar base64 a bytes
    const byteCharacters = atob(base64Data);
    const byteNumbers = new Array(byteCharacters.length);
    for (let i = 0; i < byteCharacters.length; i++) {
        byteNumbers[i] = byteCharacters.charCodeAt(i);
    }
    const byteArray = new Uint8Array(byteNumbers);
    const blob = new Blob([byteArray], { type: 'application/pdf' });
    const blobUrl = URL.createObjectURL(blob);

    // --- 1️⃣ Abrir en nueva pestaña ---
    window.open(blobUrl, '_blank');

    // --- 2️⃣ Forzar diálogo de descarga ---
    const link = document.createElement('a');
    link.href = blobUrl;
    link.download = nombreArchivo;
    document.body.appendChild(link);
    link.click();

    // --- 3️⃣ Limpieza ---
    document.body.removeChild(link);
    setTimeout(() => URL.revokeObjectURL(blobUrl), 5000);
}


// Manejar clic en el botón "Ver PDF"
$('#newAdmissionsTable').on('click', '.ver-pdf', function () {
    const base64Data = $(this).data('pdf');
    abrirYDescargarPDFBase64(base64Data);
});

$("#newAdmissionsTable").on("input", ".editable-field", function() {
    let value = $(this).val();
    let name = $(this).attr("name");
    if(name === 'controlNo') {
        value = capitalizeAll(value);
        $(this).val(value);
    }
    // Puedes agregar más condiciones si tienes otros campos que necesiten formato
});

$("#newAdmissionsTable").on("click", ".approveStudent", function(e) {
    e.preventDefault();

    let $row = $(this).closest("tr");
    let admissionId = $(this).data("id");
    let table = $("#newAdmissionsTable").DataTable();
    let rowData = table.row($row).data(); // <- Obtiene todos los datos de esa fila

    // Recorremos todos los campos editables de la fila y los almacenamos en un objeto
    $row.find(".editable-field").each(function() {
        let name = $(this).attr("name");
        let value = $(this).val();
        rowData[name] = value;
    });

    // Validar que controlNo no esté vacío
    if (!rowData.controlNo) {
        errorAlert('El número de control no puede estar vacío.');
        return;
    }

    confirmAlert('¿Seguro que desea aprobar esta solicitud?', 'Sí', 'No', function() {
        // Puedes ajustar el endpoint si deseas mantenerlo dinámico
        sendFetch(
            'https://primary-production-3210.up.railway.app/webhook-test/1016d26c-dd89-4428-ac51-f98bbaf5e9de',
            'POST',
            { 
                action: 'approveAdmission', 
                id: admissionId,
                data: rowData // Enviamos todos los campos como objeto
            }
        )
        .then(data => {
            if (data.success) {
                successAlertAuto('Solicitud aprobada con éxito');
                $('#newAdmissionsTable').DataTable().ajax.reload();
            } else {
                errorAlert(data.error || 'Error al aprobar la solicitud');
            }
        })
        .catch(() => {
            errorAlert('Error al conectar con el servidor.');
        });
    });
});

$("#newAdmissionsTable").on("click", ".deleteApplication", function(e) {
    e.preventDefault();
    let admissionId = $(this).data("id");

    confirmAlert('¿Seguro que desea eliminar esta solicitud?', 'Sí', 'No', function() { 
        sendFetch(callback, 'POST', { action: 'deleteAdmission', id: admissionId })
        .then(data => {
            if (data.success) {
                successAlertAuto('Solicitud eliminada con éxito');
                $('#newAdmissionsTable').DataTable().ajax.reload();
            } else {
                errorAlert(data.error || 'Error al eliminar la solicitud');
            }
        })
        .catch(() => {
            errorAlert('Error al conectar con el servidor.');
        });
    });
});