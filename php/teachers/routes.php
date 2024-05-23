<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
        
        case 'getTeachers':

            $getTeachers = new TeachersControl($con, $sesion);
            $teachers = $getTeachers->GetTeachers();

            header('Content-Type: application/json');
            echo json_encode($teachers);

            break;

        case 'updateTeacherData': 

            $teacherEditData = $_POST['teacherEditData'];
            parse_str($teacherEditData, $teacherData);

            $updateTeacherData = new TeachersControl($con, $sesion);
            $update = $updateTeacherData->UpdateTeacherData($teacherData);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteTeacher':
                
            $teacherId = $_POST['teacherId'];
    
            $deleteTeacher = new TeachersControl($con, $sesion);
            $delete = $deleteTeacher->DeleteTeacher($teacherId);
    
            header('Content-Type: application/json');
            echo json_encode($delete);
    
            break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){

    $action = $_GET['action'];

    switch ($action){
        
        case 'getTeacherData':

            $idTeacher = $_GET['teacherId'];

            $getTeacher = new TeachersControl($con, $sesion);
            $teacher = $getTeacher->GetTeacher($idTeacher);

            header('Content-Type: application/json');
            echo json_encode($teacher);

            break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}