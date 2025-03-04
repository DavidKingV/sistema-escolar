<?php
require __DIR__.'/../php/vendor/autoload.php';
require __DIR__.'/../backend/controllers/studentsController.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();
$students = new StudentsController($connection);

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
    }
}