<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\PaymentsController;

$connection = new DBConnection();
$payments = new PaymentsController($connection);

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
        case 'AddPayment':
            $paymentData = $data['data'];
            parse_str($paymentData, $paymentDataArray);
            $add = $payments->addPayment($paymentDataArray);
            responseJson($add);
            break;

        case 'VerifyTaxData':
            $studentId = $data['data'];
            $verify = $payments->verifyTaxData($studentId);
            responseJson($verify);
            break;

        case 'getStudentsPayMount':
            $get = $payments->getStudentsPayMount();
            responseJson($get);
            break;

        case 'setStudentPayMount':
            $studentId = $data['data']['studentId'];
            $amount = $data['data']['amount'];
            $set = $payments->setStudentPayMount($studentId, $amount);
            responseJson($set);
            break;

        case 'savePaymentDays':
            $studentId = $data['data']['studentId'];
            $paymentDay = $data['data']['paymentDay'];
            $paymentConcept = $data['data']['paymentConcept'];
            $paymentAmount = $data['data']['paymentAmount'];
            $save = $payments->savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount);
            responseJson($save);
            break;

        case 'VerifyMonthlyPayment':
            $studentId = $data['studentId'];
            $verify = $payments->verifyMonthlyPayment($studentId);
            responseJson($verify);
            break;

        case 'GetPaymentHistory':
            $studentId = $data['studentId'];
            $history = $payments->getPaymentHistory($studentId);
            responseJson($history);
            break;

        case 'CheckIfPaymentMade':
            $studentId = $data['data']['studentId'];
            $paymentDay = $data['data']['paymentDay'];
            $check = $payments->checkIfPaymentMade($studentId, $paymentDay);
            responseJson($check);
            break;


        case 'sendPaymentReceipt':
            $studentId = $data['studentId'];
            $paymentId = $data['paymentId'];
            $send = $payments->sendPaymentReceipt($studentId, $paymentId);
            responseJson($send);
            break;

        case 'SendPaymentByEmail':
            $studentId = $data['studentId'];
            $paymentId = $data['paymentId'];
            $send = $payments->sendPaymentByEmail($studentId, $paymentId);
            responseJson($send);
            break;

        default:
            responseJson(['error' => 'Unknown action']);
            break;
    }
}

?>