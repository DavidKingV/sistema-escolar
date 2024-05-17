<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/../db.php';

use \Firebase\JWT\JWT;
use Firebase\JWT\Key;

function cargarVariablesEnv() {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

class StudentsControl {
    
}