<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\CarreersController;

$connection = new DBConnection();
$carreers = new CarreersController($connection);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){

        case 'getCareers':
            $careers = $carreers->getCareers();

            header('Content-Type: application/json');
            echo json_encode($careers);

            break;
        
        case 'addCareer':
            $carreerData = $_POST['carreerData'];
            parse_str($carreerData, $carreerDataArray);

            $add = $carreers->addCarreer($carreerDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'updateCarreer':
            $carreerDataEdit = $_POST['carreerDataEdit'];
            parse_str($carreerDataEdit, $carreerDataEditArray);

            $update = $carreers->updateCarreer($carreerDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteCarreer':
            $idCarreer = $_POST['idCarreer'];

            $delete = $carreers->deleteCarreer($idCarreer);

            header('Content-Type: application/json');
            echo json_encode($delete);

            break;

        case 'getSubject':
            $carreerId = $_POST['carreerId'];

            $subjects = $carreers->getSubjects($carreerId);

            header('Content-Type: application/json');
            echo json_encode($subjects);

            break;

        case 'getChildSubject':
            $subjectID = $_POST['subjectId'];

            $subjects = $carreers->getChildSubjects($subjectID);
    
            header('Content-Type: application/json');
            echo json_encode($subjects);
    
            break;

        case 'addSubjectsCarreer':
            $subjectsCarreer = $_POST['subjectAddData'];
            parse_str($subjectsCarreer, $subjectsCarreerArray);

            $add = $carreers->addSubjectsCarreer($subjectsCarreerArray);

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

            $career = $carreers->getCareer($idCarreer);

            header('Content-Type: application/json');
            echo json_encode($career);

            break;

    }

}