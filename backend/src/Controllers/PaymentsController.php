<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\auth;
use Facturapi\Facturapi;
use Facturapi\Exceptions\Facturapi_Exception;

class PaymentsController{
    private $connection;
    private $payments;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection;
        $this->payments = new PaymentsModel($dbConnection);
    }

    public function verifyTaxData($studentId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->verifyTaxData($studentId);
    }

    public function getFacturApiData($clientId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        try {
            $facturapi = new Facturapi($_ENV['FACTURAPI_KEY']);
            $response = $facturapi->Customers->retrieve($clientId);
        } catch (Facturapi_Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage());
        }
        return $response;
    }

    public function addFactuarapi($clientId, $taxSystem, $invoiceData){
        $use = " ";
        $validTaxSystems = [605, 606, 608, 611, 612, 614, 607, 615, 625];
        $generalTaxSystems = [601, 603, 620, 621, 622, 623, 624, 626];
        $extraTaxSystems = [610, 616];

        if (in_array($taxSystem, $validTaxSystems)) {
            $use = "D10";
        } elseif (in_array($taxSystem, $extraTaxSystems)) {
            $use = "S01";
        } elseif (in_array($taxSystem, $generalTaxSystems)) {
            $use = "G03";
        }

        try{
            $facturapi = new Facturapi($_ENV['FACTURAPI_KEY']);
            $invoice = $facturapi->Invoices->create([
                "customer" => $clientId,
                "items" => [
                    [
                        "quantity" => "1",
                        "product" => [
                            "description" => $invoiceData['paymentConcept'],
                            "product_key" => "86121800",
                            "price" => $invoiceData['paymentTotal'],
                            "unit_key" => "E48",
                            "unit_name" => "Servicio",
                            "taxes" => [
                                [
                                    "rate" => 0.16,
                                    "type" => "IVA",
                                    "factor" => "Exento"
                                ]
                            ]
                        ]
                    ]
                ],
                "payment_form" => $invoiceData['paymentMethod'],
                "use" => $use,
            ]);
            $response = array("success" => true, "message" => "Factura generada exitosamente", "id" => $invoice->id);
        }catch(Facturapi_Exception $e){
            $response = array("success" => false, "message" => $e->getMessage());
        }finally{
            return $response;
        }
    }

    public function addPayment($paymentDataArray){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }

        $paymentConcept = $paymentDataArray['paymentConcept'];

        if (!empty($paymentDataArray['subjectConcept'])) {
            // subjectConcept está definido y no vacío
            $paymentConcept .= ' ' . $paymentDataArray['subjectConcept'];
            
            if (!empty($paymentDataArray['childSubjectName'])) {
                $paymentConcept .= '-' . $paymentDataArray['childSubjectName'];
            }
        } else {
            // subjectConcept no definido o vacío → usar paymentMonth
            if (!empty($paymentDataArray['paymentMonth'])) {
                $paymentConcept .= ' ' . $paymentDataArray['paymentMonth'];
            }
        }

        $date = $paymentDataArray['paymentDate'] ?? date('Y-m-d');
        $extra = $paymentDataArray['paymentExtra'] ?? 0;
        $registredBy = $_SESSION['userId'] ?? NULL;

        $this->convertDate($date);

        $paymentInvoice = $paymentDataArray['paymentInvoice'] === '1';
        $isInvoice = $paymentInvoice ? true : false;

        return $this->payments->addPayment(
            $paymentDataArray['studentName'],
            $date,
            $paymentDataArray['paymentMethod'],
            $isInvoice,
            $paymentConcept,
            $paymentDataArray['paymentPrice'],
            $extra,
            $paymentDataArray['paymentTotal'],
            $paymentDataArray['paymentComments'] ?? '',
            $registredBy
        );
    }

    private function convertDate($date){
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        return array('year' => $year, 'month' => $month);
    }

    public function getStudentsPayMount(){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->getStudentsPayMount();
    }

    public function savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount);
    }

    public function setStudentPayMount($studentId, $amount){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->setStudentPayMount($studentId, $amount);
    }

    public function verifyMonthlyPayment($studentId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->verifyMonthlyPayment($studentId);
    }

    public function getPaymentHistory($studentId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->getPaymentHistory($studentId);
    }

    public function checkIfPaymentMade($studentId, $paymentDay)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->checkIfPaymentMade($studentId, $paymentDay);
    
    }

    public function sendPaymentReceipt($studentId, $paymentId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->sendPaymentReceipt($studentId, $paymentId);
    }

    public function sendPaymentByEmail($studentId, $paymentId){
        $verifySession = auth::check();
        if(!$verifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->sendPaymentByEmail($studentId, $paymentId);
    }
}
