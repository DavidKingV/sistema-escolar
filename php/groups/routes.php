<?php
require_once(__DIR__.'/../../../vendor/autoload.php');
include __DIR__.'/index.php';

use \Firebase\JWT\JWT;
use \Firebase\JWT\Key;

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action){
        
        case 'getGroups':

            $getGroups = new GroupsControl($con, $sesion);
            $groups = $getGroups->GetGroups();

            header('Content-Type: application/json');
            echo json_encode($groups);

            break;

        case 'addGroup':

            $groupData = $_POST['groupData'];
            parse_str($groupData, $groupDataArray);

            $addGroup = new GroupsControl($con, $sesion);
            $add = $addGroup->AddGroup($groupDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);

            break;

        case 'updateGroup': 

            $groupDataEdit = $_POST['groupDataEdit'];
            parse_str($groupDataEdit, $groupDataEditArray);

            $updateGroupData = new GroupsControl($con, $sesion);
            $update = $updateGroupData->UpdateGroup($groupDataEditArray);

            header('Content-Type: application/json');
            echo json_encode($update);

            break;

        case 'deleteGroup':
                
            $groupId = $_POST['groupId'];
    
            $deleteGroup = new GroupsControl($con, $sesion);
            $delete = $deleteGroup->DeleteGroup($groupId);
    
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

            $getGroupData = new GroupsControl($con, $sesion);
            $groupData = $getGroupData->GetGroupData($groupId);

            header('Content-Type: application/json');
            echo json_encode($groupData);

            break;

        case 'getGroupsJson':

            $getGroups = new GroupsControl($con, $sesion);
            $groups = $getGroups->GetGroupsJson();

            header('Content-Type: application/json');
            echo json_encode($groups);

            break;
    }
}