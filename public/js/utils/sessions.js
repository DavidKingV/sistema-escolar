import { confirmCloseSession, loadingAlert, successAlert, errorAlert } from './alerts.js';
import { enviarPeticionAjax } from './ajax.js';

var phpPath = '/backend/login/routes.php';

function openInNewWindow(url) {
    const newWindow = window.open(url, '_blank', 'width=800,height=600');

    // Espera a que la nueva ventana se haya cargado completamente antes de enviar el mensaje
    newWindow.onload = function() {
        newWindow.postMessage({ success: true, message: "Sesión cerrada" }, "*");
        newWindow.close();
    };
}

$("#endSession").click(function(e){
    e.preventDefault();

    confirmCloseSession("¿Estás seguro de cerrar sesión?", "Cerrar sesión", "Cancelar", function(){
        loadingAlert();

        enviarPeticionAjax(phpPath, "POST", { action: "logout" })
            .done(function(data) {
                Swal.close();
                if (data.success) {
                    if(data.microsoftLogout){
                        openInNewWindow("https://login.microsoftonline.com/ff8c5e54-d300-4681-8870-a4805a435d2a/oauth2/v2.0/logout");

                        window.addEventListener('message', function(event) {
                            if (event.data.success) {
                                window.location.href = "/index.php";
                                successAlert(event.data.message);
                            }
                        }, false);
                    }
                    window.location.href = "/public/index.php?sesion=close";
                } else {
                    errorAlert(data.message);
                }
            })
            .fail(function(jqXHR, textStatus, errorThrown) {
                Swal.close();
                errorAlert("Error en la petición AJAX");
            });
        });
});