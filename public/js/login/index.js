function openInNewWindow(url) {
    window.open(url, '_blank', 'width=800,height=600');
}

function login(data){
    $.ajax({
        url: 'backend/login/routes.php',
        type: 'POST',
        data: {data: data, action: 'login'},
    }).done(function(response){
        if(response.success){
            Swal.fire({
                icon: 'success',
                title: 'Login Success',
                text: 'Redirecting to Dashboard',
                showConfirmButton: false,
                timer: 1500
            }).then(function(){
                window.location.href = 'dashboard.php';
            });
        }else{
            Swal.fire({
                icon: 'error',
                title: 'Error al iniciar sesion',
                text: response.message
            });
        }
    }).fail(function(){
        alert('Error');
    })
}

$("#openInNewWindow").on("click", function(event) {
    event.preventDefault();
    openInNewWindow("backend/login/MicrosoftLogin.php");
});

window.addEventListener('message', function(event) {
    if (event.data.MiAccto) {
        // Redirigir a inicio.php
        window.location.href = 'dashboard.php';
    } else if (event.data.error) {
        // Manejar errores de autenticación
        alert('Authentication failed');
    } else{
        // Manejar otros mensajes
        alert('Unknown message');
    }
}, false);

$("#loginForm").submit(function(e){
    e.preventDefault();
    var data = $(this).serialize();
    login(data);
});