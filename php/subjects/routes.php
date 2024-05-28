<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
    
        case 'getSubjects':
            $SubjectsControl = new SubjectsControl($con, $sesion);
            $subjects = $SubjectsControl->GetSubjects();
            echo json_encode($subjects);
            break;

        case 'addSubject':
            $subjectData = $_POST['subjectData'];
            parse_str($subjectData, $subjectDataArray);

            $SubjectsControl = new SubjectsControl($con, $sesion);
            $addSubject = $SubjectsControl->AddSubject($subjectDataArray);

            header('Content-Type: application/json');
            echo json_encode($addSubject);
            break;

        case 'updateSubjectData':
            $subjectDataEdit = $_POST['subjectDataEdit'];
            parse_str($subjectDataEdit, $subjectDataEditArray);

            $SubjectsControl = new SubjectsControl($con, $sesion);
            $updateSubjectData = $SubjectsControl->UpdateSubjectData($subjectDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($updateSubjectData);
            break;

        case 'deleteSubject':
            $subjectId = $_POST['subjectId'];

            $SubjectsControl = new SubjectsControl($con, $sesion);
            $deleteSubject = $SubjectsControl->DeleteSubject($subjectId);

            header('Content-Type: application/json');
            echo json_encode($deleteSubject);
            break;
        
    }

}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){
    
        case'getSubjectData':

            $subjectId = $_GET['subjectId'];

            $SubjectsControl = new SubjectsControl($con, $sesion);
            $subjectData = $SubjectsControl->GetSubjectData($subjectId);

            header('Content-Type: application/json');
            echo json_encode($subjectData);
            break;
    }

}