<?php
require __DIR__.'/../backend/vendor/autoload.php';
require __DIR__.'/../backend/controllers/studentsController.php';
require __DIR__.'/../backend/controllers/subjectsController.php';
require __DIR__.'/../backend/controllers/practicalHoursController.php';
require __DIR__.'/../backend/controllers/groupsController.php';
require __DIR__.'/../backend/controllers/gradesController.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();
$students = new StudentsController($connection);

$subjects = new SubjectsController($connection);

$practicalHours = new PracticalHoursController($connection);

$groups = new GroupsController($connection);

$grades = new GradesController($connection);

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
        case 'getStudentName':
            $studentId = $data['studentId'] ?? null;
            responseJson($students->getStudentName($studentId));
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

        case 'addEvent':
            $eventData = $data['eventData'] ?? null;
            parse_str($eventData, $eventData);
            responseJson($practicalHours->addEvent($eventData));
            break;
        case 'getStudentsHours':
            responseJson($practicalHours->studentsHours());
            break;
        case 'getStudentlHoursData':
            $studentId = $data['studentId'] ?? null;
            responseJson($practicalHours->getStudentlHoursData($studentId));
            break;
        case 'getEventDetails' :
            $eventId = $data['eventId'] ?? null;            
            responseJson($practicalHours->getEventDetails($eventId));
            break;
        case 'confirmHours':
            $hoursData = $data['hoursData'] ?? null;
            parse_str($hoursData, $hoursData);
            responseJson($practicalHours->confirmHours($hoursData));
            break;
        case 'addStudentHours':
            $hoursData = $data['data'] ?? null;
            parse_str($hoursData, $hoursData);
            responseJson($practicalHours->addStudentHours($hoursData));
            break;
        case 'deteleEvent':
            $hoursData = $data['hoursData'] ?? null;
            parse_str($hoursData, $hoursData);
            responseJson($practicalHours->deteleEvent($hoursData));
            break;

        case 'getNoGroupStudentsList':
            responseJson($groups->getNoGroupStudentsList());
            break;
        case 'addSchedule':
            $scheduleData = $data['scheduleData'] ?? null;
            parse_str($scheduleData, $scheduleData);
            responseJson($groups->addSchedule($scheduleData));
            break;
        case 'getSchedulesGroup':
            $groupId = $data['groupId'] ?? null;
            responseJson($groups->getSchedulesGroup($groupId));
            break;

        case 'addMakeOverGrade':
            $makeOverData = $data['gradesData'] ?? null;
            parse_str($makeOverData, $makeOverData);
            responseJson($grades->addMakeOverGrade($makeOverData));
            break;
        case 'getMakeOverDetails':
            $makeOverId = $data['makeOverId'] ?? null;
            responseJson($grades->getMakeOverGrades($makeOverId));
            break;
    }
}