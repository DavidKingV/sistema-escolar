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

try {
    $tokens = $microsoft->getToken($_REQUEST['code'], Session::get("state"));
    $microsoft->setAccessToken($tokens->access_token);
    
    $user = (new User);

    if($tokens->access_token) {
        
        echo "<script>
             window.opener.postMessage({ MiAccto: '{$tokens->access_token}' }, '*');
            window.close();
        </script>";
        

    } else {
        throw new Exception('User data not found');
    }
} catch (Exception $e) {
    echo "<script>
        window.opener.postMessage({ error: 'authentication_failed' }, '*');
        window.close();
    </script>";
}
?>