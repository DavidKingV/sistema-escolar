<?php
require_once(__DIR__.'/../vendor/autoload.php');
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

        case 'addTeacher':

            $teacherData = $_POST['teacherData'];
            parse_str($teacherData, $teacher);

            $addTeacher = new TeachersControl($con, $sesion);
            $add = $addTeacher->AddTeacher($teacher);

            header('Content-Type: application/json');
            echo json_encode($add);

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

        case 'getTeachersUsers':

            $getTeachersUsers = new TeachersControl($con, $sesion);
            $teachers = $getTeachersUsers->GetTeachersUsers();

            header('Content-Type: application/json');
            echo json_encode($teachers);

            break;

        case'verifyTeacherUser':

            $teacherUserAdd = $_POST['teacherUserAdd'];

            $verifyTeacherUser = new TeachersControl($con, $sesion);
            $verify = $verifyTeacherUser->VerifyTeacherUser($teacherUserAdd);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

        case 'addTeacherUser':
                
            $teacherUserAdd = $_POST['teacherUserData'];
            parse_str($teacherUserAdd, $teacherUserAddArray);

            $addTeacherUser = new TeachersControl($con, $sesion);
            $add = $addTeacherUser->AddTeacherUser($teacherUserAddArray);
    
            header('Content-Type: application/json');
            echo json_encode($add);
    
            break;

        case 'desactivateTeacherUser':
                
            $teacherUserId = $_POST['teacherUserId'];

            $desactivateTeacherUser = new TeachersControl($con, $sesion);
            $desactivate = $desactivateTeacherUser->DesactivateTeacherUser($teacherUserId);

            header('Content-Type: application/json');
            echo json_encode($desactivate);

            break;

        case 'reactivateTeacherUser':
                
            $teacherUserId = $_POST['teacherUserId'];

            $reactivateTeacherUser = new TeachersControl($con, $sesion);
            $reactivate = $reactivateTeacherUser->ReactivateTeacherUser($teacherUserId);

            header('Content-Type: application/json');
            echo json_encode($reactivate);

            break;

        case 'UpdateTeacherUserData':

            $teacherUserEditData = $_POST['teacherUserEditData'];
            parse_str($teacherUserEditData, $teacherUserDataArray);

            $updateTeacherUserData = new TeachersControl($con, $sesion);
            $update = $updateTeacherUserData->UpdateTeacherUserData($teacherUserDataArray);

            header('Content-Type: application/json');
            echo json_encode($update);

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