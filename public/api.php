<?php
require __DIR__.'/../php/vendor/autoload.php';
require __DIR__.'/../backend/controllers/studentsController.php';
require __DIR__.'/../backend/controllers/subjectsController.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();
$students = new StudentsController($connection);

$subjects = new SubjectsController($connection);

function responseJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

$data = json_decode(file_get_contents('php://input'), true);

if ($data === null) {
    // Si no se detecta JSON, usa $_POST para acceder a los datos
    $data = $_POST;
}

if(!isset($data['action'])){
    responseJson(['error' => 'Action not specified']);
    exit;
}else{
    switch ($data['action']) {
        
        case 'updateStatus':
            $statusData = $data['statusData'] ?? null;
            parse_str($statusData, $statusData);
            responseJson($students->updateStatus($statusData));
            break;

        case 'getStudentsListSelect':
            responseJson($students->getStudentsListSelect());
            break;


        case 'getSubjectsListSelect':
            $careerId = $data['careerId'] ?? null;
            responseJson($subjects->getSubjectsListSelect($careerId));
            break;
        case 'getChildSubject':
            $subjectId = $data['subjectId'] ?? null;
            responseJson($subjects->getChildSubject($subjectId));
            break;
        case'subjectsListTable':
            $careerId = $data['careerId'] ?? null;
            responseJson($subjects->subjectsListTable($careerId));
            break;

        case 'addSubjectCareer':
            $subjectData = $data['subjectAddData'] ?? null;
            parse_str($subjectData, $subjectData);
            responseJson($subjects->addSubjectCareer($subjectData));
            break;
    }
}