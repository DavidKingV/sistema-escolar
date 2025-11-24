<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\PracticalHoursController;

$connection = new DBConnection();
$practicalHours = new PracticalHoursController($connection);

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
        case 'deleteHour':
            $eventId = $data['hourId'];
            $result = $practicalHours->deleteHour($eventId);
            responseJson($result);
            break;

        default:
            responseJson(['error' => 'Unknown action']);
            break;
    }
}

?>