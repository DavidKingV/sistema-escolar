<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\SubjectsController;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
    
        case 'getSubjects':
            $subjectsController = new SubjectsController($connection);
            $subjects = $subjectsController->GetSubjects();

            header('Content-Type: application/json');
            echo json_encode($subjects);


            break;

        case 'addSubject':
            $subjectData = $_POST['subjectData'];
            parse_str($subjectData, $subjectDataArray);

            $subjectsController = new SubjectsController($connection);
            $addSubject = $subjectsController->AddSubject($subjectDataArray);

            header('Content-Type: application/json');
            echo json_encode($addSubject);
            break;

        case 'updateSubjectData':
            $subjectDataEdit = $_POST['subjectDataEdit'];
            parse_str($subjectDataEdit, $subjectDataEditArray);

            $subjectsController = new SubjectsController($connection);
            $updateSubjectData = $subjectsController->UpdateSubjectData($subjectDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($updateSubjectData);
            break;

        case 'deleteSubject':
            $subjectId = $_POST['subjectId'];

            $subjectsController = new SubjectsController($connection);
            $deleteSubject = $subjectsController->DeleteSubject($subjectId);

            header('Content-Type: application/json');
            echo json_encode($deleteSubject);
            break;

        case 'addSubjectChild':
            $subjectChildData = $_POST['subjectChildData'];
            parse_str($subjectChildData, $subjectChildDataArray);

            $subjectsController = new SubjectsController($connection);
            $addSubjectChild = $subjectsController->AddSubjectChild($subjectChildDataArray);
    
            header('Content-Type: application/json');
            echo json_encode($addSubjectChild);
            break;

        case 'updateSubjectChild' :
            $subjectUpdateChildData = $_POST['subjectUpdateChildData'];
            parse_str($subjectUpdateChildData, $subjectChildDataEditArray);

            $subjectsController = new SubjectsController($connection);
            $updateSubjectChild = $subjectsController->UpdateSubjectChild($subjectChildDataEditArray);
    
            header('Content-Type: application/json');
            echo json_encode($updateSubjectChild);
            break;

        default:
        error_log("Acción no reconocida: " . $action);
        echo json_encode(array("success" => false, "message" => "Acción no reconocida"));
        
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){
    
        case'getSubjectData':

            $subjectId = $_GET['subjectId'];

            $subjectsController = new SubjectsController($connection);
            $subjectData = $subjectsController->GetSubjectData($subjectId);

            header('Content-Type: application/json');
            echo json_encode($subjectData);
            break;

        case 'getChildSubjectsData':

            $subjectFatherId = $_GET['subjectFatherId'];
            $subjectChildId = $_GET['subjectChildId'];

            $subjectsController = new SubjectsController($connection);
            $subjects = $subjectsController->GetSubjectChildData($subjectFatherId, $subjectChildId);

            header('Content-Type: application/json');
            echo json_encode($subjects);
            break;
    }

}