<?php
require_once(__DIR__.'/../vendor/autoload.php');

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\TeachersController;

$connection = new DBConnection();
$teachersController = new TeachersController($connection);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){

        case 'getTeachers':
            $teachers = $teachersController->getTeachers();
            header('Content-Type: application/json');
            echo json_encode($teachers);
            break;

        case 'addTeacher':
            $teacherData = $_POST['teacherData'];
            parse_str($teacherData, $teacher);
            $add = $teachersController->addTeacher($teacher);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'updateTeacherData':
            $teacherEditData = $_POST['teacherEditData'];
            parse_str($teacherEditData, $teacherData);
            $update = $teachersController->updateTeacherData($teacherData);
            header('Content-Type: application/json');
            echo json_encode($update);
            break;

        case 'deleteTeacher':
            $teacherId = $_POST['teacherId'];
            $delete = $teachersController->deleteTeacher($teacherId);
            header('Content-Type: application/json');
            echo json_encode($delete);
            break;

        case 'getTeachersUsers':
            $teachers = $teachersController->getTeachersUsers();
            header('Content-Type: application/json');
            echo json_encode($teachers);
            break;

        case 'verifyTeacherUser':
            $teacherUserAdd = $_POST['teacherUserAdd'];
            $verify = $teachersController->verifyTeacherUser($teacherUserAdd);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        case 'addTeacherUser':
            $teacherUserAdd = $_POST['teacherUserData'];
            parse_str($teacherUserAdd, $teacherUserAddArray);
            $add = $teachersController->addTeacherUser($teacherUserAddArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'desactivateTeacherUser':
            $teacherUserId = $_POST['teacherUserId'];
            $desactivate = $teachersController->desactivateTeacherUser($teacherUserId);
            header('Content-Type: application/json');
            echo json_encode($desactivate);
            break;

        case 'reactivateTeacherUser':
            $teacherUserId = $_POST['teacherUserId'];
            $reactivate = $teachersController->reactivateTeacherUser($teacherUserId);
            header('Content-Type: application/json');
            echo json_encode($reactivate);
            break;

        case 'UpdateTeacherUserData':
            $teacherUserEditData = $_POST['teacherUserEditData'];
            parse_str($teacherUserEditData, $teacherUserDataArray);
            $update = $teachersController->updateTeacherUserData($teacherUserDataArray);
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
            $teacher = $teachersController->getTeacher($idTeacher);
            header('Content-Type: application/json');
            echo json_encode($teacher);
            break;

        default:
            echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}
