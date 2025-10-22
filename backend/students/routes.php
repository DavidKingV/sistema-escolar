<?php
require_once(__DIR__.'/../vendor/autoload.php');
require_once __DIR__.'/verify.php';

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\StudentsController;

session_start();

$connection = new DBConnection();
$studentsController = new StudentsController($connection);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $action = $_POST['action'];

    switch ($action){
        case 'getStudents':
            $students = $studentsController->getStudents();
            header('Content-Type: application/json');
            echo json_encode($students);
            break;

        case 'GetStudentsNames':
            $studentsNames = $studentsController->getStudentsNames();
            header('Content-Type: application/json');
            echo json_encode($studentsNames);
            break;

        case 'addStudent':
            $studentData = $_POST['studentData'];
            parse_str($studentData, $studentDataArray);
            $add = $studentsController->addStudent($studentDataArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'deleteStudent':
            $studentId = $_POST['studentId'];
            $delete = $studentsController->deleteStudent($studentId);
            header('Content-Type: application/json');
            echo json_encode($delete);
            break;

        case 'updateStudent':
            $studentData = $_POST['studentData'];
            parse_str($studentData, $studentDataArray);
            $update = $studentsController->updateStudent($studentDataArray);
            header('Content-Type: application/json');
            echo json_encode($update);
            break;

        case 'getStudentsUsers':
            $students = $studentsController->getStudentsUsers();
            header('Content-Type: application/json');
            echo json_encode($students);
            break;

        case 'getStudentsMicrosoftUsers':
            $studentsMicrosoftUsers = $studentsController->getMicrosoftStudentsUsers();
            header('Content-Type: application/json');
            echo json_encode($studentsMicrosoftUsers);
            break;

        case 'verifyStudentUser':
            $studentUser = $_POST['studentUserAdd'];
            $verify = $studentsController->verifyStudentUser($studentUser);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        case 'addStudentUser':
            $studentUserData = $_POST['studentUserData'];
            parse_str($studentUserData, $studentDataArray);
            $add = $studentsController->addStudentUser($studentDataArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'updateStudentUser':
            $studentEditUserData = $_POST['studentUserData'];
            parse_str($studentEditUserData, $studentEditDataArray);
            $update = $studentsController->updateStudentUser($studentEditDataArray);
            header('Content-Type: application/json');
            echo json_encode($update);
            break;

        case 'desactivateStudentUser':
            $studentId = $_POST['studentId'];
            $desactivate = $studentsController->desactivateStudentUser($studentId);
            header('Content-Type: application/json');
            echo json_encode($desactivate);
            break;

        case 'reactivateStudentUser':
            $studentId = $_POST['studentId'];
            $reactivate = $studentsController->reactivateStudentUser($studentId);
            header('Content-Type: application/json');
            echo json_encode($reactivate);
            break;

        case 'getStudentGrades':
            $studentId = $_POST['studentId'];
            $grades = $studentsController->getStudentGrades($studentId);
            header('Content-Type: application/json');
            echo json_encode($grades);
            break;

        case 'addGradeStudent':
            $gradeData = $_POST['studentGradeData'];
            parse_str($gradeData, $gradeDataArray);
            $add = $studentsController->addGradeStudent($gradeDataArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'addStudentGroup':
            $studentGroupData = $_POST['studentGroupData'] ?? NULL;
            parse_str($studentGroupData, $studentGroupDataArray);
            $add = $studentsController->addStudentGroup($studentGroupDataArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'searchMicrosoftUser':
            $displayName = $_POST['data']['studentName'] ?? '';
            $search = $studentsController->searchMicrosoftUser($displayName);
            header('Content-Type: application/json');
            echo json_encode($search);
            break;

        case 'assignMicrosoftUserToStudent':
            $data = parse_str($_POST['data'], $dataArray);
            $studentId = $dataArray['studentId'] ?? '';
            $microsoftUserId = $dataArray['microsoftId'] ?? '';
            $microsoftDisplayName = $dataArray['displayName'] ?? '';
            $microsoftEmail = $dataArray['mail'] ?? '';
            $assign = $studentsController->assignMicrosoftUserToStudent($studentId, $microsoftUserId, $microsoftDisplayName, $microsoftEmail);
            header('Content-Type: application/json');
            echo json_encode($assign);
            break;

        default:
            echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}

if($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])){
    $action = $_GET['action'];

    switch ($action){
        case 'getStudentData':
            $studentId = $_GET['studentId'];
            $student = $studentsController->getStudent($studentId);
            header('Content-Type: application/json');
            echo json_encode($student);
            break;

        case 'getSubjectsNames':
            $carrerId = $_GET['carrerId'];
            $subjects = $studentsController->getSubjectsNames($carrerId);
            header('Content-Type: application/json');
            echo json_encode($subjects);
            break;

        case 'getChildSubjectsNames':
            $idSubject = $_GET['idSubject'];
            $childSubjects = $studentsController->getChildSubjectsNames($idSubject);
            header('Content-Type: application/json');
            echo json_encode($childSubjects);
            break;

        case 'verifyToken':
            $studentId = $_GET['studentId'];
            $studentSecretKey = $_GET['token'];
            $verifyToken = new AdvancedStudentsControl();
            $verify = $verifyToken->VerifyIdStudentId($studentId, $studentSecretKey);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        case 'verifyGroupStudent':
            $studentIdGroup = $_GET['studentIdGroup'];
            $verify = $studentsController->verifyGroupStudent($studentIdGroup);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        case 'getGroupsNames':
            $groups = $studentsController->getGroupsNames();
            header('Content-Type: application/json');
            echo json_encode($groups);
            break;

        case 'searchMicrosoftUser':
            $displayName = $_GET['displayName'];
            $search = $studentsController->searchMicrosoftUser($displayName);
            header('Content-Type: application/json');
            echo json_encode($search);
            break;

        default:
            echo json_encode(array("success" => false, "message" => "Acción no válida"));
    }
}
