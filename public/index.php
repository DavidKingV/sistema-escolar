<?php

require __DIR__ . '/../backend/vendor/autoload.php';

use Vendor\Schoolarsystem\Core\Router;

// Crear el router
$router = new Router();

// Registrar rutas
require __DIR__ . '/../backend/routes/web.php';
require __DIR__ . '/../backend/routes/api.php';

// Obtener URI
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$base = dirname($_SERVER['SCRIPT_NAME']);

if ($base !== '/' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

$uri = $uri ?: '/';

// Despachar solicitud
$router->dispatch($_SERVER['REQUEST_METHOD'], $uri);