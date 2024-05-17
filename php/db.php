<?php

$host="localhost";
$user="root";
$password="";
$db="control_escolar";

$con = new mysqli($host,$user,$password,$db);

if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

?>
