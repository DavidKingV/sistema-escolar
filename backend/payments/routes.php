<?php
require_once(__DIR__.'/../vendor/autoload.php');
session_start();

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Controllers\PaymentsController;

$connection = new DBConnection();
$payments = new PaymentsController($connection);

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action) {
        case 'AddPayment':
            $paymentData = $_POST['data'];
            parse_str($paymentData, $paymentDataArray);
            $add = $payments->addPayment($paymentDataArray);
            header('Content-Type: application/json');
            echo json_encode($add);
            break;

        case 'VerifyTaxData':
            $studentId = $_POST['data'];
            $verify = $payments->verifyTaxData($studentId);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        case 'getStudentsPayMount':
            $get = $payments->getStudentsPayMount();
            header('Content-Type: application/json');
            echo json_encode($get);
            break;

        case 'setStudentPayMount':
            $data = $_POST['data'];
            $studentId = $data['studentId'];
            $amount = $data['amount'];
            $set = $payments->setStudentPayMount($studentId, $amount);
            header('Content-Type: application/json');
            echo json_encode($set);
            break;

        case 'VerifyMonthlyPayment':
            $studentId = $_POST['studentId'];
            $verify = $payments->verifyMonthlyPayment($studentId);
            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        default:
            break;
    }
}
