<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
    
        case 'getSubjects':
            $SubjectsControl = new SubjectsControl($connection);
            $subjects = $SubjectsControl->GetSubjects();

            header('Content-Type: application/json');
            echo json_encode($subjects);


            break;

        case 'addSubject':
            $subjectData = $_POST['subjectData'];
            parse_str($subjectData, $subjectDataArray);

            $SubjectsControl = new SubjectsControl($connection);
            $addSubject = $SubjectsControl->AddSubject($subjectDataArray);

            header('Content-Type: application/json');
            echo json_encode($addSubject);
            break;

        case 'updateSubjectData':
            $subjectDataEdit = $_POST['subjectDataEdit'];
            parse_str($subjectDataEdit, $subjectDataEditArray);

            $SubjectsControl = new SubjectsControl($connection);
            $updateSubjectData = $SubjectsControl->UpdateSubjectData($subjectDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($updateSubjectData);
            break;

        case 'deleteSubject':
            $subjectId = $_POST['subjectId'];

            $SubjectsControl = new SubjectsControl($connection);
            $deleteSubject = $SubjectsControl->DeleteSubject($subjectId);

            header('Content-Type: application/json');
            echo json_encode($deleteSubject);
            break;

        case 'addSubjectChild':
            $subjectChildData = $_POST['subjectChildData'];
            parse_str($subjectChildData, $subjectChildDataArray);
    
            $SubjectsControl = new SubjectsControlChild($connection);
            $addSubjectChild = $SubjectsControl->AddSubjectChild($subjectChildDataArray);
    
            header('Content-Type: application/json');
            echo json_encode($addSubjectChild);
            break;

        case 'updateSubjectChild' :
            $subjectUpdateChildData = $_POST['subjectUpdateChildData'];
            parse_str($subjectUpdateChildData, $subjectChildDataEditArray);
    
            $SubjectsControl = new SubjectsControlChild($connection);
            $updateSubjectChild = $SubjectsControl->UpdateSubjectChild($subjectChildDataEditArray);
    
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

            $SubjectsControl = new SubjectsControl($connection);
            $subjectData = $SubjectsControl->GetSubjectData($subjectId);

            header('Content-Type: application/json');
            echo json_encode($subjectData);
            break;

        case 'getChildSubjectsData':

            $subjectFatherId = $_GET['subjectFatherId'];
            $subjectChildId = $_GET['subjectChildId'];

            $SubjectsControl = new SubjectsControlChild($connection);
            $subjects = $SubjectsControl->GetSubjectChildData($subjectFatherId, $subjectChildId);

            header('Content-Type: application/json');
            echo json_encode($subjects);
            break;
    }

}