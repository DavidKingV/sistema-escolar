<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

cargarVariablesEnv();
$secret_key = $_ENV['KEY'];
$tiempo_vida = $_ENV['LIFE_TIME'];

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
        
    }

}