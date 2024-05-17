function login(data){
    $.ajax({
        url: 'php/login/routes.php',
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

$("#loginForm").submit(function(e){
    e.preventDefault();
    var data = $(this).serialize();
    login(data);
});