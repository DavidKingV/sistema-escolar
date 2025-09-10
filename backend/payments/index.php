<?php
require_once(__DIR__.'/../vendor/autoload.php');

session_start();

use Vendor\Schoolarsystem\auth;
use Vendor\Schoolarsystem\loadEnv;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\userData;

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
        $VerifySession = auth::check();
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
        $VerifySession = auth::check();
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
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
        return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{

            $date = $paymentDataArray['paymentDate'] ?? date('Y-m-d');
            $extra = $paymentDataArray['paymentExtra'] ?? 0;
            $registredBy = $_SESSION['userId'] ?? NULL;
            
            $dateArray = $this->ConvertDate($date);

            $paymentInvoice = $paymentDataArray['paymentInvoice'] === '1';

            switch($paymentInvoice){
                case false:
                    $isInvoice = false;
                    break;
                case true:
                    $isInvoice = true; 
                    break;
            }

            if($isInvoice === false){                
                try {

                    $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, concept, cost, extra, total, registred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param("ississiiiI", $paymentDataArray['studentName'], $date, $paymentDataArray['paymentMethod'], $isInvoice, $paymentDataArray['paymentConcept'], $paymentDataArray['paymentPrice'], $extra, $paymentDataArray['paymentTotal'], $registredBy);
                    $stmt->execute();

                            
                    if ($stmt->affected_rows > 0) {
                        $response = array("success" => true, "message" => "Pago registrado exitosamente");
                    } else {
                        $response = array("success" => false, "message" => "Error al guardar el pago");
                    }

                } catch (mysqli_sql_exception $e) {
                            $response = array("success" => false, "message" => "Error al procesar la solicitud de pago");
                }finally {
                    if (isset($stmt)) {
                        $stmt->close();
                    }
                }
                  
            }else{
                try {

                    $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, concept, cost, extra, total, registred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $this->connection->prepare($sql);
                    $stmt->bind_param("ississiiiI", $paymentDataArray['studentName'], $date, $paymentDataArray['paymentMethod'], $isInvoice, $paymentDataArray['paymentConcept'], $paymentDataArray['paymentPrice'], $extra, $paymentDataArray['paymentTotal'], $registredBy);
                    $stmt->execute();
                    
                    if ($stmt->affected_rows > 0) {
                        $response = array("success" => true, "message" => "Pago registrado exitosamente");
                    } else {
                        $response = array("success" => false, "message" => "Error al registrar el pago");
                    }
                } catch (mysqli_sql_exception $e) {
                    $response = array("success" => false, "message" => "Error al procesar la solicitud de pago");
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

    public function GetStudentsPayMount(){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            try {
                $sql = "SELECT students.id, students.nombre, students_payments_amounts.monthly_amount FROM students LEFT JOIN students_payments_amounts ON students.id = students_payments_amounts.id_student ORDER BY id;";
                $stmt = $this->connection->prepare($sql);
                $stmt->execute();
                $result = $stmt->get_result();
            
                $response = array();
                if(!$result){
                    return array("success" => false, "message" => "Error al obtener los grupos");
                }else{
                    if($result->num_rows > 0){
                        while($row = $result->fetch_assoc()){
                            $response[] = array(
                                "success" => true,
                                "id" => $row['id'],
                                "name" => $row['nombre'],
                                "amount" => $row['monthly_amount']
                            );
                        }
                    }else{
                        $response = array("success" => false, "message" => "No se encontraron grupos");
                    }
                    $this->connection->close();
                    return $response;
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

    public function SetStudentPayMount($studentId, $amount){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            try {
                $sql = "INSERT INTO students_payments_amounts (id_student, monthly_amount) VALUES (?, ?) ON DUPLICATE KEY UPDATE monthly_amount = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("iii", $studentId, $amount, $amount);
                $stmt->execute();
            
                $response = ($stmt->affected_rows > 0)
                    ? array("success" => true, "message" => "Monto actualizado exitosamente")
                    : array("success" => false, "message" => "Error al actualizar el monto");
                           
            } catch (mysqli_sql_exception $e) {
                $response = array("success" => false, "message" => $e->getMessage());
            }finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
            }
            
            return $response;
        }
    }

    public function VerifyMonthlyPayment($studentId){
        $VerifySession = auth::check();
        if(!$VerifySession['success']){
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }else{
            try {
                $sql = "SELECT * FROM students_payments_amounts WHERE id_student = ?";
                $stmt = $this->connection->prepare($sql);
                $stmt->bind_param("i", $studentId);
                $stmt->execute();
                $result = $stmt->get_result();
            
                $response = ($result->num_rows > 0)
                    ? array("success" => true, "monthly_amount" => $result->fetch_assoc()['monthly_amount'])
                    : array("success" => false, "message" => "No se encontraron pagos pendientes");
                           
            } catch (mysqli_sql_exception $e) {
                $response = array("success" => false, "message" => "Error al procesar la solicitud");
            }finally {
                if (isset($stmt)) {
                    $stmt->close();
                }
            }
        }
            return $response;
    }
}

?>