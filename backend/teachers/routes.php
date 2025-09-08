<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
        
        case 'getTeachers':

            $getTeachers = new TeachersControl($connection);
            $teachers = $getTeachers->GetTeachers();

            header('Content-Type: application/json');
            echo json_encode($teachers);

            break;

        case 'addTeacher':

            $teacherData = $_POST['teacherData'];
            parse_str($teacherData, $teacher);

            $addTeacher = new TeachersControl($connection);
            $add = $addTeacher->AddTeacher($teacher);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'updateTeacherData': 

            $teacherEditData = $_POST['teacherEditData'];
            parse_str($teacherEditData, $teacherData);

            $updateTeacherData = new TeachersControl($connection);
            $update = $updateTeacherData->UpdateTeacherData($teacherData);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteTeacher':
                
            $teacherId = $_POST['teacherId'];
    
            $deleteTeacher = new TeachersControl($connection);
            $delete = $deleteTeacher->DeleteTeacher($teacherId);
    
            header('Content-Type: application/json');
            echo json_encode($delete);
    
            break;

        case 'getTeachersUsers':

            $getTeachersUsers = new TeachersControl($connection);
            $teachers = $getTeachersUsers->GetTeachersUsers();

            header('Content-Type: application/json');
            echo json_encode($teachers);

            break;

        case'verifyTeacherUser':

            $teacherUserAdd = $_POST['teacherUserAdd'];

            $verifyTeacherUser = new TeachersControl($connection);
            $verify = $verifyTeacherUser->VerifyTeacherUser($teacherUserAdd);

            header('Content-Type: application/json');
            echo json_encode($verify);

            break;

        case 'addTeacherUser':
                
            $teacherUserAdd = $_POST['teacherUserData'];
            parse_str($teacherUserAdd, $teacherUserAddArray);

            $addTeacherUser = new TeachersControl($connection);
            $add = $addTeacherUser->AddTeacherUser($teacherUserAddArray);
    
            header('Content-Type: application/json');
            echo json_encode($add);
    
            break;

        case 'desactivateTeacherUser':
                
            $teacherUserId = $_POST['teacherUserId'];

            $desactivateTeacherUser = new TeachersControl($connection);
            $desactivate = $desactivateTeacherUser->DesactivateTeacherUser($teacherUserId);

            header('Content-Type: application/json');
            echo json_encode($desactivate);

            break;

        case 'reactivateTeacherUser':
                
            $teacherUserId = $_POST['teacherUserId'];

            $reactivateTeacherUser = new TeachersControl($connection);
            $reactivate = $reactivateTeacherUser->ReactivateTeacherUser($teacherUserId);

            header('Content-Type: application/json');
            echo json_encode($reactivate);

            break;

        case 'UpdateTeacherUserData':

            $teacherUserEditData = $_POST['teacherUserEditData'];
            parse_str($teacherUserEditData, $teacherUserDataArray);

            $updateTeacherUserData = new TeachersControl($connection);
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

            $getTeacher = new TeachersControl($connection);
            $teacher = $getTeacher->GetTeacher($idTeacher);

            header('Content-Type: application/json');
            echo json_encode($teacher);

            break;

        default:
        echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}