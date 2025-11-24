<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\StudentsController;

$connection = new DBConnection();
$studentsController = new StudentsController($connection);

// ---------------------------------
// Función para responder en JSON
// ---------------------------------
function responseJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ---------------------------------
// Unificar datos según método HTTP
// ---------------------------------
$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $data = $_GET;
        break;

    case 'POST':
        $json = json_decode(file_get_contents('php://input'), true);
        $data = $json ?: $_POST;
        break;

    case 'PUT':
    case 'DELETE':
        $data = json_decode(file_get_contents('php://input'), true) ?? [];
        break;

    default:
        responseJson(['error' => 'Método HTTP no soportado']);
}

// ---------------------------------
// Validar acción
// ---------------------------------
if (!isset($data['action'])) {
    responseJson(['error' => 'Action not specified']);
}

$action = $data['action'];

// ---------------------------------
// Router de acciones
// ---------------------------------
switch ($action) {

    // -------------------------------
    // GET
    // -------------------------------
    case 'getStudents':
        responseJson($studentsController->getStudents());
        break;

    case 'getStudentData':
        responseJson($studentsController->getStudent($data['studentId'] ?? null));
        break;

    case 'getSubjectsNames':
        responseJson($studentsController->getSubjectsNames($data['carrerId'] ?? null));
        break;

    case 'getChildSubjectsNames':
        responseJson($studentsController->getChildSubjectsNames($data['idSubject'] ?? null));
        break;

    case 'verifyGroupStudent':
        responseJson($studentsController->verifyGroupStudent($data['studentIdGroup'] ?? null));
        break;

    // -------------------------------
    // POST (form-data / JSON)
    // -------------------------------
    case 'addStudent':
        parse_str($data['studentData'] ?? '', $studentDataArray);
        responseJson($studentsController->addStudent($studentDataArray));
        break;
    case 'getStudentsNames':
        responseJson($studentsController->getStudentsNames());
        break;
    
    case 'getStudentsUsers':
        responseJson($studentsController->getStudentsUsers());
        break;

    case 'updateStudent':
        parse_str($data['studentData'] ?? '', $studentDataArray);
        responseJson($studentsController->updateStudent($studentDataArray));
        break;

    case 'deleteStudent':
        responseJson($studentsController->deleteStudent($data['studentId'] ?? null));
        break;

    case 'addStudentUser':
        parse_str($data['studentUserData'] ?? '', $userData);
        responseJson($studentsController->addStudentUser($userData));
        break;

    case 'updateStudentUser':
        parse_str($data['studentUserData'] ?? '', $userData);
        responseJson($studentsController->updateStudentUser($userData));
        break;

    case 'desactivateStudentUser':
        responseJson($studentsController->desactivateStudentUser($data['studentId'] ?? null));
        break;

    case 'reactivateStudentUser':
        responseJson($studentsController->reactivateStudentUser($data['studentId'] ?? null));
        break;

    case 'getStudentGrades':
        responseJson($studentsController->getStudentGrades($data['studentId'] ?? null));
        break;

    case 'addGradeStudent':
        parse_str($data['studentGradeData'] ?? '', $gradeData);
        responseJson($studentsController->addGradeStudent($gradeData));
        break;

    case 'addStudentGroup':
        parse_str($data['studentGroupData'] ?? '', $groupData);
        responseJson($studentsController->addStudentGroup($groupData));
        break;

    // -------------------------------
    // Microsoft Graph
    // -------------------------------
    case 'searchMicrosoftUser':
        $displayName = $data['displayName'] ?? ($data['studentName'] ?? '');
        responseJson($studentsController->searchMicrosoftUser($displayName));
        break;

    case 'assignMicrosoftUserToStudent':
        parse_str($data['data'] ?? '', $parsed);
        responseJson($studentsController->assignMicrosoftUserToStudent(
            $parsed['studentId'] ?? '',
            $parsed['microsoftId'] ?? '',
            $parsed['displayName'] ?? '',
            $parsed['mail'] ?? ''
        ));
        break;

    case 'getStudentsMicrosoftUsers':
        responseJson($studentsController->getMicrosoftStudentsUsers());
        break;

    default:
        responseJson(["success" => false, "message" => "Acción no válida"]);
}
