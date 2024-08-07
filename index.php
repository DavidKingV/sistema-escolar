<?php
require_once(__DIR__.'/php/vendor/autoload.php');

use Vendor\Schoolarsystem\auth;

session_start();

$VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);

$userId = $VerifySession['userId'] ?? NULL;

if ($VerifySession['success'] && $userId != NULL) {
  header('Location: dashboard.php?session=restored');
  exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="assets/css/index.css" rel="stylesheet">
    <title>Inicio</title>
</head>
<body>

    <nav class="navbar bg-body-tertiary">
        <div class="container-fluid">
          <a class="navbar-brand" href="#">
            <img src="assets/img/escudo.png" alt="Logo" width="50" height="50" class="d-inline-block align-text-mid">
            ESMEFIS Centro Universitario
          </a>
        </div>
    </nav>
    
      <!-- Login -->

        <!-- Section: Design Block -->
<section class="text-center text-lg-start">
  
    <!-- Jumbotron -->
    <div class="container py-4">
      <div class="row g-0 align-items-center" id="items-body">
        <div class="col-lg-6 mb-5 mb-lg-0">
          <div class="card cascading-right bg-body-tertiary" style="
              backdrop-filter: blur(30px);
              ">
            <div class="card-body p-5 shadow-5 text-center">
              <h2 class="fw-bold mb-5">Login</h2>              
  
                <!-- Email input -->
                <form id="loginForm">
                    <div data-mdb-input-init class="form-outline mb-4">
                        <input type="text" id="user" name="user" class="form-control" />
                        <label class="form-label" for="form3Example3">Usuario</label>
                      </div>
        
                      <!-- Password input -->
                      <div data-mdb-input-init class="form-outline mb-4">
                        <input type="password" id="password" name="password" class="form-control" />
                        <label class="form-label" for="form3Example4">Contraseña</label>
                      </div>    
                      
                      <div class="form-floating mb-3 py-2">
                        <a href="javascript:void(0);" id="openInNewWindow">
                            <img src="assets/img/microsoft-sign.png" alt="Descripción de la imagen">
                        </a>                                
                      </div>  
        
                      <!-- Submit button -->
                      <button type="submit" data-mdb-button-init data-mdb-ripple-init class="btn btn-primary btn-block mb-4">
                        Entrar
                      </button>
                </form>
                
  
                <!-- Register buttons -->
                <div class="text-center">
                  <p>¿Olvisate la Contraseña?</p>
                  <p>Por favor comunicate con el admnistrador</p>
                </div>
            </div>
          </div>
        </div>
  
        <div class="col-lg-6 mb-5 mb-lg-0">
          <img src="assets/img/login.svg" class="w-100 rounded-4 shadow-4"
            alt="" />
        </div>
      </div>
    </div>
    <!-- Jumbotron -->
  </section>
  <!-- Section: Design Block -->


</body>
</html>

<!-- Boostrap -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- jquery -->
<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.js" integrity="sha256-J8ay84czFazJ9wcTuSDLpPmwpMXOm573OUtZHPQqpEU=" crossorigin="anonymous"></script>

<!-- javascript -->
<script src="js/login/index.js"></script>