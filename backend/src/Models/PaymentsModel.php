<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use mysqli_sql_exception;

class PaymentsModel{
    private $connection;

    public function __construct(DBConnection $dbConnection) {
        $this->connection = $dbConnection->getConnection();
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
            $stmt->bind_param("ississiii", $studentId, $date, $paymentMethod, $isInvoice, $concept, $cost, $extra, $total, $registredBy);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                $response = array("success" => true, "message" => "Pago registrado exitosamente");
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
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }
}
