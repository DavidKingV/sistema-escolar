<?php
// ponytail: DEBUG TEMPORAL — quitar estas 3 líneas cuando se resuelva el 500.
ini_set('display_errors', '1');
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Vendor\Schoolarsystem\loadEnv;
use myPHPnotes\Microsoft\Models\User;
use myPHPnotes\Microsoft\Auth;
use myPHPnotes\Microsoft\Handlers\Session;
use GuzzleHttp\Client;

loadEnv::cargar();

$tenant = Session::get("tenant_id") ?? $_ENV['TENANT_ID'];
$clientId = Session::get("client_id") ?? $_ENV['CLIENT_ID'];
$clientSecret = Session::get("client_secret") ?? $_ENV['CLIENT_SECRET'];
$redirectUri = Session::get("redirect_uri") ?? $_ENV['CALLBACK_PATH'];
$scopes = Session::get("scopes") ?? ["User.ReadBasic.All", "offline_access"];

$microsoft = new Auth($tenant, $clientId, $clientSecret, $redirectUri, $scopes);

try {
    $tokens = $microsoft->getToken($_REQUEST['code'], Session::get("state"));

    // ponytail: Microsoft devuelve un JSON de error (secret expirado, etc.) en vez del token;
    // sin esto $tokens->access_token es un Error fatal no capturado = 500 en blanco.
    if (!is_object($tokens) || empty($tokens->access_token)) {
        $detail = is_object($tokens) && isset($tokens->error_description)
            ? $tokens->error_description
            : 'Token endpoint no devolvió access_token';
        throw new Exception($detail);
    }

    $microsoft->setAccessToken($tokens->access_token);
    $user = (new User);

    $baseUrl = $_ENV['BASE_URL'] ?? '.';
    echo "<script>
         window.opener.postMessage({ MiAccto: '{$tokens->access_token}', redirect: '{$baseUrl}/dashboard.php' }, '*');
        window.close();
    </script>";
} catch (\Throwable $e) {
    error_log('MicrosoftAuth error: ' . $e->getMessage());
    $detail = json_encode($e->getMessage());
    echo "<script>
        window.opener.postMessage({ error: 'authentication_failed', detail: {$detail} }, '*');
        window.close();
    </script>";
}
?>