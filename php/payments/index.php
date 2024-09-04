<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;

use Facturapi\Facturapi;
use Facturapi\Http\BaseClient;
use Facturapi\Exceptions\Facturapi_Exception;

loadEnv::cargar();

class PaymentsControl{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
    }

    public function VerifyTaxData($studentId){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            try {
                $sql = "SELECT * FROM invoice_data WHERE id_student = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $result = $stmt->get_result();
            
                $response = ($result->num_rows > 0)
                    ? array("success" => true, "message" => "Datos de facturación encontrados", "data" => $result->fetch_assoc())
                    : array("success" => false, "message" => "No se encontraron datos de facturación");
                           
            } catch (mysqli_sql_exception $e) {
                $response = array("success" => false, "message" => "Error al procesar la solicitud");
            }finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
            }
            
            return $response;
        }
    }

    public function GetFacturApiData($clientId){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            try {
                $facturapi = new Facturapi($_ENV['FACTURAPI_KEY']);
                $response = $facturapi->Customers->retrieve($clientId);                
            } catch (Facturapi_Exception $e) {
                $response = array("success" => false, "message" => $e->getMessage());
            }
            return $response;
        }
    }

    public function AddFactuarapi($clientId, $taxSystem, $invoiceData){

        $use = " ";
        $validTaxSystems = [605, 606, 608, 611, 612, 614, 607, 615, 625];
        $generalTaxSystems = [ 601, 603, 620, 621, 622, 623, 624, 626 ];
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

    public function AddPayment($paymentDataArray){
        $VerifySession = auth::verify($_COOKIE['auth'] ?? NULL);
        if(!$VerifySession['success']){
        return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{

            $date = $paymentDataArray['paymentDate'] ?? date('Y-m-d');
            $extra = $paymentDataArray['paymentExtra'] ?? 0;
            $registredBy = 'Admin';
            
            $dateArray = $this->ConvertDate($date);

            switch($paymentDataArray['paymentInvoice']){
                case '0':
                    $isInvoice = 0;
                    break;
                case '1':
                    $isInvoice = 1;
                    break;
            }

            if($isInvoice != 0){
                $verifyFacturapiData = $this->GetFacturApiData($paymentDataArray['fiscalId']);
                $clientId = $verifyFacturapiData -> id;
                $taxSystem = $verifyFacturapiData -> tax_system;

                if($clientId){
                    $addInvoice = $this->AddFactuarapi($clientId, $taxSystem, $paymentDataArray);
                    if($addInvoice['success']){
                        try {                            

                            $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, invoice_id, concept, cost, extra, total, registred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                            $stmt = $this->connection->prepare($sql);
                            $stmt->bind_param("ississiiis", $paymentDataArray['studentName'], $date, $paymentDataArray['paymentMethod'], $isInvoice, $addInvoice['id'], $paymentDataArray['paymentConcept'], $paymentDataArray['paymentPrice'], $extra, $paymentDataArray['paymentTotal'], $registredBy);
                            $stmt->execute();

                            
                            if ($stmt->affected_rows > 0) {
                                $payed = 1;
                                try{
                                    $monthQuery = "INSERT INTO monthly_payments (id_student, month, year, concept, payed, extra) VALUES (?, ?, ?, ?, ?, ?)";
                                    $stmtMonth = $this->connection->prepare($monthQuery);
                                    $stmtMonth->bind_param("iiisii", $paymentDataArray['studentName'], $dateArray['month'], $dateArray['year'], $paymentDataArray['paymentConcept'], $payed, $extra);
                                    $stmtMonth->execute();

                                    if ($stmtMonth->affected_rows > 0) {
                                        $response = array("success" => true, "message" => "Pago registrado exitosamente");
                                    } else {
                                        $response = array("success" => false, "message" => "Error al guardar el pago");
                                    }
                                }catch(mysqli_sql_exception $e){
                                    $response = array("success" => false, "message" => "Error al procesar la solicitud");
                                }finally{
                                    if (isset($stmtMonth)) {
                                        $stmtMonth->close();
                                    }
                                }
                            } else {
                                $response = array("success" => false, "message" => "Error al procesar la solicitud");
                            }

                        } catch (mysqli_sql_exception $e) {
                            $response = array("success" => false, "message" => "Error al procesar la solicitud");
                        }finally {
                            if (isset($stmt)) {
                                $stmt->close();
                            }
                        }
                    }else{
                        $response = array("success" => false, "message" => $addInvoice['message']);
                    }

                    return $response;
                    
                }
            }else{
                try {

                    $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, concept, cost, extra, total, registred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param("issisiiis", $paymentDataArray['studentName'], $date, $paymentDataArray['paymentMethod'], $isInvoice, $paymentDataArray['paymentConcept'], $paymentDataArray['paymentPrice'], $extra, $paymentDataArray['paymentTotal'], $registredBy);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        $payed = 1;
                        try{                            
                            $monthQuery = "INSERT INTO monthly_payments (id_student, month, year, concept, payed, extra) VALUES (?, ?, ?, ?, ?, ?)";
                            $stmtMonth = $this->connection->prepare($monthQuery);
                            $stmtMonth->bind_param("iiisii", $paymentDataArray['studentName'], $dateArray['month'], $dateArray['year'], $paymentDataArray['paymentConcept'], $payed, $extra);
                            $stmtMonth->execute();

                            if ($stmtMonth->affected_rows > 0) {
                                $response = array("success" => true, "message" => "Pago registrado exitosamente");
                            } else {
                                $response = array("success" => false, "message" => "Error al guardar el pago");
                            }
                        }catch(mysqli_sql_exception $e){
                            if($e->getCode() == 1062){
                                $response = array("success" => false, "message" => "El pago ya ha sido registrado, por favor verifique los datos");
                            }else{
                                $response = array("success" => false, "message" => "Error al procesar la solicitud");
                            }
                        }finally{
                            if (isset($stmtMonth)) {
                                $stmtMonth->close();
                            }
                        }
                    } else {
                        $response = array("success" => false, "message" => "Error al registrar el pago");
                    }
                } catch (mysqli_sql_exception $e) {
                    $response = array("success" => false, "message" => "Error al procesar la solicitud");
                }finally {
                    if (isset($stmt)) {
                        $stmt->close();
                    }
                }
                return $response;
            }
        } 
    }

    public function ConvertDate($date){

        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));       

        return array('year' => $year, 'month' => $month);
    }
}

?>