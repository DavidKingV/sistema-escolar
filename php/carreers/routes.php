<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){

        case 'getCareers':
            $getCareers = new CareersControl($con, $sesion);
            $careers = $getCareers->getCareers();

            header('Content-Type: application/json');
            echo json_encode($careers);

            break;
        
        case 'addCareer':
            $carreerData = $_POST['carreerData'];
            parse_str($carreerData, $carreerDataArray);

            $addCarreer = new CareersControl($con, $sesion);
            $add = $addCarreer->addCarreer($carreerDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

    
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){

        case 'getCareerData':
            $idCarreer = $_GET['idCarreer'];

            $getCareer = new CareersControl($con, $sesion);
            $career = $getCareer->getCareer($idCarreer);

            header('Content-Type: application/json');
            echo json_encode($career);

            break;
    
    }

}