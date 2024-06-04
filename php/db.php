<?php

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$host=$_ENV['DB_HOST'];
$user=$_ENV['DB_USER'];
$password=$_ENV['DB_PASSWORD'];
$db=$_ENV['DB_NAME'];

$con = new mysqli($host,$user,$password,$db);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

?>
