<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){

        case 'getCareers':
            $getCareers = new CareersControl($connection);
            $careers = $getCareers->getCareers();

            header('Content-Type: application/json');
            echo json_encode($careers);

            break;
        
        case 'addCareer':
            $carreerData = $_POST['carreerData'];
            parse_str($carreerData, $carreerDataArray);

            $addCarreer = new CareersControl($connection);
            $add = $addCarreer->addCarreer($carreerDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'updateCarreer':
            $carreerDataEdit = $_POST['carreerDataEdit'];
            parse_str($carreerDataEdit, $carreerDataEditArray);

            $updateCarreer = new CareersControl($connection);
            $update = $updateCarreer->updateCarreer($carreerDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteCarreer':
            $idCarreer = $_POST['idCarreer'];

            $deleteCarreer = new CareersControl($connection);
            $delete = $deleteCarreer->deleteCarreer($idCarreer);

            header('Content-Type: application/json');
            echo json_encode($delete);

            break;

        case 'getSubject':
            $carreerId = $_POST['carreerId'];

            $getSubjects = new CareersControl($connection);
            $subjects = $getSubjects->getSubjects($carreerId);

            header('Content-Type: application/json');
            echo json_encode($subjects);

            break;

        case 'getChildSubject':
            $subjectID = $_POST['subjectId'];

            $getSubjects = new CareersControl($connection);
            $subjects = $getSubjects->getChildSubjects($subjectID);
    
            header('Content-Type: application/json');
            echo json_encode($subjects);
    
            break;

        case 'addSubjectsCarreer':
            $subjectsCarreer = $_POST['subjectAddData'];
            parse_str($subjectsCarreer, $subjectsCarreerArray);

            $addSubjectsCarreer = new CareersControl($connection);
            $add = $addSubjectsCarreer->addSubjectsCarreer($subjectsCarreerArray);

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

            $getCareer = new CareersControl($connection);
            $career = $getCareer->getCareer($idCarreer);

            header('Content-Type: application/json');
            echo json_encode($career);

            break;
    
    }

}