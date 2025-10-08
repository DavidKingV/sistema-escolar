<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\AdmissionsController;

$connection = new DBConnection();
$admissions = new AdmissionsController($connection);

$data = json_decode(file_get_contents('php://input'), true);

function responseJson($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

if ($data === null) {
    $data = $_POST;
}

if(!isset($data['action'])){
    responseJson(['error' => 'Action not specified']);
    exit;
}else{
    switch ($data['action']) {
        
        case 'getAllNewAdmissions':
            $result = $admissions->getAllNewAdmissions();
            responseJson($result);
            break;

        default:
            responseJson(['error' => 'Unknown action']);
            break;
    }
}

?>