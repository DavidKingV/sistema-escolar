<?php
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

$baseUrl = $_ENV['BASE_URL'] ?? '.';

try {
    $tokens = $microsoft->getToken($_REQUEST['code'], Session::get("state"));
    $microsoft->setAccessToken($tokens->access_token);

    $user = (new User);

    if ($tokens->access_token) {
        // Guardamos el token en la sesión del servidor en vez de exponerlo en el JS
        Session::set('microsoft_access_token', $tokens->access_token);
        $_SESSION['logged_in'] = true;

        header("Location: {$baseUrl}/dashboard.php");
        exit;
    } else {
        throw new Exception('User data not found');
    }
} catch (Exception $e) {
    header("Location: {$baseUrl}/login.php?error=authentication_failed");
    exit;
}
?>