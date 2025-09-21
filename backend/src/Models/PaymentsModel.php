<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\FacturapiModel;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Facturapi\Facturapi;
use mysqli_sql_exception;

class PaymentsModel{
    private $connection;
    private $facturapiModel;
    private $studentsModel;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
        $this->facturapiModel = new FacturapiModel($dbConnection);
        $this->studentsModel = new StudentsModel($dbConnection);
    }

    public function verifyTaxData($studentId){
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
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function addPayment($studentId, $date, $paymentMethod, $isInvoice, $concept, $cost, $extra, $total, $registredBy){
        try {
            $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, concept, cost, extra, total, registred_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("issisiiii", $studentId, $date, $paymentMethod, $isInvoice, $concept, $cost, $extra, $total, $registredBy);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $paymentId = $stmt->insert_id;
                $studentData = $this->studentsModel->getStudentById($studentId);
                $studentMail = $studentData['studentData']['email'] ?? NULL;
                //almacena todos los datos del pago en un array
                $paymentData = [
                    'date' => $date,
                    'paymentForm' => $paymentMethod,
                    'products' => [
                        [
                            'product' => $concept,
                            'unitPrice' => $cost + $extra,
                            'quantity' => 1,
                            'subTotal' => $total,
                        ],
                        // puedes añadir más productos aquí
                    ],
                    'email' => $studentMail,
                ];
                
                if($isInvoice){
                    $invoiceResponse = $this->facturapiModel->createReceipt($paymentData, $paymentId);
                    if($invoiceResponse['success']){
                        $response = array("success" => true, "message" => "Pago e invoice registrados exitosamente", "invoiceId" => $invoiceResponse['receipt']);
                    }else{
                        $response = array("success" => false, "message" => "Pago registrado pero error al generar la factura: " . $invoiceResponse['message']);
                    }
                }else{
                    $response = array("success" => true, "message" => "Pago registrado exitosamente");
                }
            } else {
                $response = array("success" => false, "message" => "Error al registrar el pago");
            }
        } catch (mysqli_sql_exception $e) {
            $response = array("success" => false, "message" => "Error al procesar la solicitud de pago");
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function getStudentsPayMount(){
        try {
            $sql = "SELECT students.id, students.nombre, students_payments_amounts.monthly_amount FROM students LEFT JOIN students_payments_amounts ON students.id = students_payments_amounts.id_student ORDER BY id;";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            $response = array();
            if(!$result){
                $response = array("success" => false, "message" => "Error al obtener los grupos");
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
            }
        } catch (mysqli_sql_exception $e) {
            $response = array("success" => false, "message" => "Error al procesar la solicitud");
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function setStudentPayMount($studentId, $amount){
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
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function verifyMonthlyPayment($studentId){
        try {
            $sql = "SELECT * FROM payments_dates WHERE id_student = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $studentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response = array(
                    "success" => true,
                    "monthly_amount" => $row['amount'],
                    "payment_day" => $row['payment_day'],
                    "concept" => $row['concept']
                );
            } else {
                $response = array("success" => false, "message" => "No se encontraron pagos pendientes");
            }
        } catch (mysqli_sql_exception $e) {
            $response = array("success" => false, "message" => "Error al procesar la solicitud");
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount){
        try {
            $sql = "INSERT INTO payments_dates (id_student, payment_day, concept, amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE payment_day = ?, concept = ?, amount = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("iisdiss", $studentId, $paymentDay, $paymentConcept, $paymentAmount, $paymentDay, $paymentConcept, $paymentAmount);
            $stmt->execute();

            $response = ($stmt->affected_rows > 0)
                ? array("success" => true, "message" => "Día de pago guardado/actualizado exitosamente")
                : array("success" => false, "message" => "Error al actualizar el día de pago");
        } catch (mysqli_sql_exception $e) {
            $response = array("success" => false, "message" => $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }
}
