<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\GroupsController;

$connection = new DBConnection();
$groups = new GroupsController($connection);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){
    $action = $_POST['action'];
    $response = null;

    switch ($action){
        case 'getGroups':
            $response = $groups->getGroups();
            break;

        case 'getGroupsStudents':
            $groupId = $_POST['groupId'];
            $response = $groups->getGroupsStudents($groupId);
            break;

        case 'addGroup':
            $groupData = $_POST['groupData'];
            parse_str($groupData, $groupDataArray);
            $response = $groups->addGroup($groupDataArray);
            break;

        case 'updateGroup':
            $groupDataEdit = $_POST['groupDataEdit'];
            parse_str($groupDataEdit, $groupDataEditArray);
            $response = $groups->updateGroup($groupDataEditArray);
            break;

        case 'deleteGroup':
            $groupId = $_POST['groupId'];
            $response = $groups->deleteGroup($groupId);
            break;

        case 'addStudentGroup':
            $groupId = $_POST['groupId'];
            $studentId = $_POST['studentId'];
            $response = $groups->addStudentGroup($groupId, $studentId);
            break;

        case 'deleteStudentGroup':
            $groupId = $_POST['groupId'];
            $studentId = $_POST['studentId'];
            $response = $groups->deleteStudentGroup($groupId, $studentId);
            break;
    }

    if(isset($response)){
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];
    $response = null;

    switch ($action) {
        case 'getGroupData':
            $groupId = $_GET['groupId'];
            $response = $groups->getGroupData($groupId);
            break;

        case 'getGroupsJson':
            $response = $groups->getGroupsJson();
            break;

        case 'getStudentsNames':
            $response = $groups->getStudentsNames();
            break;
    }

    if(isset($response)){
        header('Content-Type: application/json');
        echo json_encode($response);
    }
}
