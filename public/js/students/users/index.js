import { FillTable, ClearInputsEditEstudents, ClearStudensAddUser, ClearStudensEditUser, AverageGrade, initializeSubjectChangeListener, HideTab, RenderAlertMessage } from '../forms.js';
import { initializeStudentDataTable, initializeStudentsUsersTable, initializeStudentsMicrosoftUsersTable, InitializeStudentGrades } from '../../datatables/index.js';
import { enviarPeticionAjaxAction } from '../../utils/ajax.js';
import { sendFetch } from '../../global/fetchCall.js';
import { errorAlert, successAlert, infoAlert, loadingSpinner, confirmAlert } from '../../utils/alerts.js';
import { validateForm, capitalizeAllWords, capitalizeAll } from '../../global/validate/index.js';

let phpPath = '../../backend/students/routes.php';


$("#studentsUsersTable").on( "click", ".toMicrosoft", function() {
    let studentId = $(this).data('id');
    let studentName = $(this).data('name');
    if(studentId && studentName){
        $("#studentUserIdMicrosoft").val(studentId);
        $("#studentUserNameMicrosoft").val(studentName);
    }
    else{
        Swal.fire({
            icon: 'error',
            title: 'ID del estudiante no proporcionado',
            text: 'Por favor proporciona un ID válido para asignar un usuario.'
        });
    }
});

$("#searchMicrosoftUser").on("click", async function() {
    const studentName = $("#studentUserNameMicrosoft").val().trim();
    const resultsDiv = $("#microsoftUserSearchResults");

    // Validación temprana: si no hay nombre, salimos sin continuar
    if (studentName === "") {
        Swal.fire({
            icon: 'error',
            title: 'Nombre del estudiante no proporcionado',
            text: 'Por favor proporciona un nombre válido para buscar el usuario.'
        });
        return; // <- evita ejecutar el resto del código
    }

    // Mostrar spinner mientras se hace la búsqueda
    resultsDiv.html(loadingSpinner(true)); // ajustado el uso

    try {
        const response = await enviarPeticionAjaxAction(
            phpPath,
            'POST',
            'searchMicrosoftUser',
            { studentName }
        );

        resultsDiv.empty(); // limpiar el spinner

        if (response.success) {
            if (response.data && response.data.success) {
                const user = response.data;

                const userHtml = `
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Usuario encontrado</h5>
                            <br>
                            <form id="microsoftUserSearchResultsForm">
                                <div class="mb-3">
                                    <label class="form-label">ID de usuario:</label>
                                    <input type="text" class="form-control" name="microsoftId" value="${user.id}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre para mostrar:</label>
                                    <input type="text" class="form-control" name="displayName" value="${user.displayName}" readonly>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Correo electrónico:</label>
                                    <input type="text" class="form-control" name="mail" value="${user.mail}" readonly>
                                </div>
                                <button type="submit" class="btn btn-primary" id="assignMicrosoftUser">Asignar este usuario</button>
                                <button type="button" class="btn btn-secondary" id="cancelMicrosoftUser">Cancelar</button>
                            </form>
                        </div>
                    </div>
                `;
                resultsDiv.append(userHtml);
            } else {
                resultsDiv.append('<div class="alert alert-info">No se encontraron usuarios de Microsoft para el nombre proporcionado.</div>');
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error en la búsqueda',
                text: response.message || 'Ocurrió un error al buscar usuarios de Microsoft.'
            });
        }
    } catch (error) {
        resultsDiv.empty();
        Swal.fire({
            icon: 'error',
            title: 'Error en la conexión',
            text: error.statusText || 'No se pudo conectar con el servidor.'
        });
    }
});


$("#microsoftUserSearchResults").on("submit", "form", async function(event) {
    event.preventDefault();
    let data = $(this).serialize();
    data += `&studentId=${$("#studentUserIdMicrosoft").val()}`;
    
    if (!data.includes('microsoftId=') || !data.includes('displayName=') || !data.includes('mail=')) {
        Swal.fire({
            icon: 'error',
            title: 'Datos incompletos',
            text: 'No se proporcionaron todos los datos necesarios para asignar el usuario.'
        });
        return;
    }

    confirmAlert(
        'Esta acción asignará el usuario de Microsoft al estudiante seleccionado. ¿Deseas continuar?',
        'Sí, asignar usuario',
        'Cancelar',
        async () => {
            try {
                showLoader();
                
                const response = await enviarPeticionAjaxAction(
                    phpPath,
                    'POST',
                    'assignMicrosoftUserToStudent',
                    data // ya no necesitas {data}, la función ya lo envuelve
                );

                hideLoader();

                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Usuario asignado',
                        text: 'El usuario de Microsoft ha sido asignado correctamente al estudiante.'
                    });

                    // (Opcional) limpiar el formulario o refrescar la vista:
                    $("#microsoftUserSearchResults").empty();
                    $("#toMicrosoftModal").modal('hide');
                    $("#studentsUsersTable").DataTable().ajax.reload(null, false); // recarga la tabla sin resetear la paginación
                    $("#studentsMicrosoftUsersTable").DataTable().ajax.reload(null, false); // recarga la tabla sin resetear la paginación
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error al asignar usuario',
                        text: response.message || 'Ocurrió un error al asignar el usuario de Microsoft al estudiante.'
                    });
                }

            } catch (error) {
                hideLoader();
                Swal.fire({
                    icon: 'error',
                    title: 'Error en la conexión',
                    text: error.statusText || 'No se pudo conectar con el servidor.'
                });
            }
        }
    );
});


const showLoader = () => {
  $("#globalLoader").fadeIn(200);
};

const hideLoader = () => {
  $("#globalLoader").fadeOut(200);
};