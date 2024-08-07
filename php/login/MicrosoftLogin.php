<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use Vendor\Schoolarsystem\loadEnv;
use myPHPnotes\Microsoft\Auth;

loadEnv::cargar();

$tenant = $_ENV['TENANT_ID'];
$client_id = $_ENV['CLIENT_ID'];
$client_secret = $_ENV['CLIENT_SECRET'];
$callback = $_ENV['CALLBACK_PATH']; 
$scopes = ["User.ReadBasic.All", "offline_access"];

$microsoft = new Auth($tenant, $client_id,  $client_secret, $callback, $scopes);

header("location: ". $microsoft->getAuthUrl()); //Redirecting to get access token

?>