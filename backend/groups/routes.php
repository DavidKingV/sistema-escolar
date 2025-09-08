<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
        
        case 'getGroups':

            $getGroups = new GroupsControl($connection);
            $groups = $getGroups->GetGroups();

            header('Content-Type: application/json');
            echo json_encode($groups);

            break;

        case 'getGroupsStudents':   
            
            $groupId = $_POST['groupId'];

            $getGroupsStudents = new GroupsControl($connection);
            $groupsStudents = $getGroupsStudents->GetGroupsStudents($groupId);

            header('Content-Type: application/json');
            echo json_encode($groupsStudents);

            break;        

        case 'addGroup':

            $groupData = $_POST['groupData'];
            parse_str($groupData, $groupDataArray);

            $addGroup = new GroupsControl($connection);
            $add = $addGroup->AddGroup($groupDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'updateGroup': 

            $groupDataEdit = $_POST['groupDataEdit'];
            parse_str($groupDataEdit, $groupDataEditArray);

            $updateGroupData = new GroupsControl($connection);
            $update = $updateGroupData->UpdateGroup($groupDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteGroup':
                
            $groupId = $_POST['groupId'];
    
            $deleteGroup = new GroupsControl($connection);
            $delete = $deleteGroup->DeleteGroup($groupId);
    
            header('Content-Type: application/json');
            echo json_encode($delete);
    
            break;

        case 'addStudentGroup':

            $groupId = $_POST['groupId'];
            $studentId = $_POST['studentId'];

            $addStudentGroup = new GroupsControl($connection);
            $add = $addStudentGroup->AddStudentGroup($groupId, $studentId);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'deleteStudentGroup': 

            $studentId = $_POST['studentId'];

            $deleteStudentGroup = new GroupsControl($connection);
            $delete = $deleteStudentGroup->DeleteStudentGroup($studentId);

            header('Content-Type: application/json');
            echo json_encode($delete);

            break;
    }

}
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {
        case 'getGroupData':
            $groupId = $_GET['groupId'];

            $getGroupData = new GroupsControl($connection);
            $groupData = $getGroupData->GetGroupData($groupId);

            header('Content-Type: application/json');
            echo json_encode($groupData);

            break;

        case 'getGroupsJson':

            $getGroups = new GroupsControl($connection);
            $groups = $getGroups->GetGroupsJson();

            header('Content-Type: application/json');
            echo json_encode($groups);

            break;

        case 'getStudentsNames':

            $getStudentsNames = new GroupsControl($connection);
            $studentsNames = $getStudentsNames->GetStudentsNames();
    
            header('Content-Type: application/json');
            echo json_encode($studentsNames);
    
            break;
    }
}