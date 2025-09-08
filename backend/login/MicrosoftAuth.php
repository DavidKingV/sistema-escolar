<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use myPHPnotes\Microsoft\Models\User;
use myPHPnotes\Microsoft\Auth;
use myPHPnotes\Microsoft\Handlers\Session;
use GuzzleHttp\Client;


$microsoft = new Auth(Session::get("tenant_id"),Session::get("client_id"),  Session::get("client_secret"), Session::get("redirect_uri"), Session::get("scopes"));

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