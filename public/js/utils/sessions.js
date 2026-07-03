import { confirmCloseSession, loadingAlert, successAlert, errorAlert } from './alerts.js';
import { enviarPeticionAjax } from './ajax.js';

var phpPath = '/backend/login/routes.php';

$("#endSession").click(function(e){
    e.preventDefault();

    confirmCloseSession("¿Estás seguro de cerrar sesión?", "Cerrar sesión", "Cancelar", function(){
        loadingAlert();

        enviarPeticionAjax(phpPath, "POST", { action: "logout" })
            .done(function(data) {
                Swal.close();
                if (data.success) {
                    if(data.microsoftLogout){
                        var redirectUri = encodeURIComponent(data.microsoft_redirect || window.location.origin + "/index.php");
                        window.location.href = "https://login.microsoftonline.com/ff8c5e54-d300-4681-8870-a4805a435d2a/oauth2/v2.0/logout?post_logout_redirect_uri=" + redirectUri;
                    } else {
                        window.location.href = data.redirect || "/public/index.php?sesion=close";
                    }
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