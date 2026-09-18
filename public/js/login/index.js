function login(data) {
  $.ajax({
    url: `${BASE_URL}/login`,
    type: "POST",
    data: {
      user: data.user,
      password: data.password,
    },
    dataType: "json",
  })
    .done(function (response) {
      if (response.success) {
        Swal.fire({
          icon: "success",
          title: "Login Success",
          text: "Redirecting to Dashboard",
          showConfirmButton: false,
          timer: 1500,
        }).then(function () {
          window.location.href = response.redirect || "dashboard.php";
        });
      } else {
        Swal.fire({
          icon: "error",
          title: "Error al iniciar sesion",
          text: response.message,
        });
      }
    })
    .fail(function () {
      alert("Error");
    });
}

$("#openInNewWindow").on("click", function (event) {
  event.preventDefault();
  window.location.href = "../backend/login/MicrosoftLogin.php";
});

window.addEventListener(
  "message",
  function (event) {
    if (event.data.MiAccto) {
      // Redirigir a inicio.php
      window.location.href = event.data.redirect || "dashboard.php";
    } else if (event.data.error) {
      // Manejar errores de autenticación
      alert(
        "Authentication failed: " + (event.data.detail || event.data.error),
      );
    } else {
      // Manejar otros mensajes
      alert("Unknown message");
    }
  },
  false,
);

$("#loginForm").submit(function (e) {
  e.preventDefault();

  const data = Object.fromEntries(
    $(this)
      .serializeArray()
      .map(({ name, value }) => [name, value]),
  );

  login(data);
});
