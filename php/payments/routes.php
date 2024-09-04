<?php
require_once(__DIR__.'/../vendor/autoload.php');
include __DIR__.'/index.php';

use Vendor\Schoolarsystem\DBConnection;

$connection = new DBConnection();

if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])){

    $action = $_POST['action'];

    switch ($action) {
        case 'AddPayment':
            $paymentData = $_POST['data'];

            parse_str($paymentData, $paymentDataArray);

            $addPayment = new PaymentsControl($connection);
            $add = $addPayment->AddPayment($paymentDataArray);

            header('Content-Type: application/json');
            echo json_encode($add);
            break;
        
        case 'VerifyTaxData':
            $studentId = $_POST['data'];

            $verifyTaxData = new PaymentsControl($connection);
            $verify = $verifyTaxData->VerifyTaxData($studentId);

            header('Content-Type: application/json');
            echo json_encode($verify);
            break;

        default:
            # code...
            break;
    }

}
?>