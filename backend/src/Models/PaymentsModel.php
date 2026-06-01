<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\FacturapiModel;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\EmailsModel;
use Vendor\Schoolarsystem\Helpers\RandomPasswords;
use Facturapi\Facturapi;
use mysqli_sql_exception;

class PaymentsModel
{
    private $connection;
    private $facturapiModel;
    private $studentsModel;
    private $emailModel;
    private $passwordsHelper;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
        $this->facturapiModel = new FacturapiModel($dbConnection);
        $this->studentsModel = new StudentsModel($dbConnection);
        $this->emailModel = new EmailsModel();
        $this->passwordsHelper = new RandomPasswords();
    }

    public function verifyTaxData($studentId)
    {
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

    public function addPayment($studentId, $date, $paymentMethod, $isInvoice, $concept, $concept_subject, $concept_subject_child, $concept_carreer, $concept_month, $cost, $extra, $total, $comments, $registredBy)
    {
        try {
            $randomPassword = $this->passwordsHelper->generateRandomPassword(12);
            $estatus = $isInvoice ? 'pending' : 'confirmed';

            $sql = "INSERT INTO students_payments (id_student, payment_date, payment_method, invoice, concept, concept_subject, concept_subject_child, concept_carreer, concept_month, cost, extra, total, comments, registred_by, password, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("isiisssssiiisiss", $studentId, $date, $paymentMethod, $isInvoice, $concept, $concept_subject, $concept_subject_child, $concept_carreer, $concept_month, $cost, $extra, $total, $comments, $registredBy, $randomPassword, $estatus);
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

                if ($isInvoice) {
                    $invoiceResponse = $this->facturapiModel->createReceipt($paymentData, $paymentId);
                    if ($invoiceResponse['success']) {
                        $response = array("success" => true, "message" => "Pago e invoice registrados exitosamente", "invoiceId" => $invoiceResponse['receipt']);
                    } else {
                        $response = array("success" => false, "message" => "Pago registrado pero error al generar la factura: " . $invoiceResponse['message']);
                    }
                } else {
                    $response = array("success" => true, "message" => "Pago registrado exitosamente", "paymentId" => $paymentId);
                }
            } else {
                $response = array("success" => false, "message" => "Error al registrar el pago" . $stmt->error);
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

    public function updatePayment(
        $paymentId,
        $cost,
        $extra,
        $total,
        $method,
        $comments
    ) {
        try {

            $sql = "
            UPDATE students_payments 
            SET
                cost = ?,
                extra = ?,
                total = ?,
                payment_method = ?,
                comments = ?
            WHERE id = ?
        ";

            $stmt = $this->connection->prepare($sql);

            $stmt->bind_param(
                "sdissi",
                $cost,
                $extra,
                $total,
                $method,
                $comments,
                $paymentId
            );

            $stmt->execute();

            if ($stmt->affected_rows >= 0) {
                return [
                    "success" => true,
                    "message" => "Pago actualizado correctamente"
                ];
            }

            return [
                "success" => false,
                "message" => "No se realizaron cambios"
            ];

        } catch (mysqli_sql_exception $e) {

            return [
                "success" => false,
                "message" => "Error al actualizar el pago"
            ];

        } finally {

            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function softDeletePayment($paymentId)
    {
        try {

            $sql = "UPDATE students_payments
                SET isDeleted = 1
                WHERE id = ?";

            $stmt = $this->connection->prepare($sql);

            $stmt->bind_param("i", $paymentId);

            $stmt->execute();

            if ($stmt->affected_rows > 0) {

                return [
                    "success" => true,
                    "message" => "Pago eliminado correctamente"
                ];

            }

            return [
                "success" => false,
                "message" => "No se pudo eliminar"
            ];

        } catch (mysqli_sql_exception $e) {

            return [
                "success" => false,
                "message" => "Error al eliminar el pago"
            ];

        }
    }

    public function cancelPayment($paymentId, $comments)
    {
        try {
            $sql = "UPDATE students_payments
                SET status    = 'cancelled',
                    comments  = ?
                WHERE id      = ?
                  AND isDeleted = 0";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("si", $comments, $paymentId);
            $stmt->execute();

            if ($stmt->affected_rows > 0) {
                return [
                    "success" => true,
                    "message" => "Recibo cancelado correctamente."
                ];
            }

            return [
                "success" => false,
                "message" => "No se pudo cancelar el recibo."
            ];

        } catch (mysqli_sql_exception $e) {
            return [
                "success" => false,
                "message" => "Error al cancelar el recibo."
            ];
        }
    }

    public function getStudentsPayMount()
    {
        try {
            $sql = "SELECT students.id, students.nombre, students_payments_amounts.monthly_amount FROM students LEFT JOIN students_payments_amounts ON students.id = students_payments_amounts.id_student ORDER BY id;";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $result = $stmt->get_result();

            $response = array();
            if (!$result) {
                $response = array("success" => false, "message" => "Error al obtener los grupos");
            } else {
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        $response[] = array(
                            "success" => true,
                            "id" => $row['id'],
                            "name" => $row['nombre'],
                            "amount" => $row['monthly_amount']
                        );
                    }
                } else {
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

    public function setStudentPayMount($studentId, $amount)
    {
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

    public function verifyMonthlyPayment($studentId)
    {
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
                $response = array("success" => true, "message" => "No se encontraron pagos");
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

    public function savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount)
    {
        try {
            $sql = "INSERT INTO payments_dates (id_student, payment_day, concept, amount) VALUES (?, ?, ?, ?) ON DUPLICATE KEY UPDATE payment_day = ?, concept = ?, amount = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("iisdiss", $studentId, $paymentDay, $paymentConcept, $paymentAmount, $paymentDay, $paymentConcept, $paymentAmount);
            $stmt->execute();

            $response = ($stmt->affected_rows > 0)
                ? array("success" => true, "message" => "Día de pago guardado/actualizado exitosamente")
                : array("success" => false, "message" => "Error al definir/actualizar el día de pago");
        } catch (mysqli_sql_exception $e) {
            $response = array("success" => false, "message" => $e->getMessage());
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
        return $response;
    }

    public function getPaymentHistory($studentId, $paymentId = null)
    {
        try {

            $sql = "
                SELECT 
                    id,
                    id_student,
                    payment_date,
                    payment_method,
                    invoice,
                    concept,
                    concept_subject,
                    concept_subject_child,
                    concept_carreer,
                    concept_month,
                    cost,
                    extra,
                    total,
                    comments,
                    status
                FROM students_payments
                WHERE id_student = ?
                AND isDeleted = 0
            ";

            $types = "i";
            $params = [$studentId];

            // Si viene paymentId, agregar filtro
            if (!empty($paymentId)) {
                $sql .= " AND id = ?";
                $types .= "i";
                $params[] = $paymentId;
            }

            $sql .= " ORDER BY payment_date DESC";

            $stmt = $this->connection->prepare($sql);

            $stmt->bind_param($types, ...$params);

            $stmt->execute();

            $result = $stmt->get_result();

            $response = [];

            while ($row = $result->fetch_assoc()) {

                $response[] = [
                    "id" => $row['id'],
                    "id_student" => $row['id_student'],
                    "payment_date" => $row['payment_date'],
                    "payment_method" => $row['payment_method'],
                    "invoice" => $row['invoice'],
                    "concept" => $row['concept'],
                    "concept_subject" => $row['concept_subject'] ?? null,
                    "concept_subject_child" => $row['concept_subject_child'] ?? null,
                    "concept_carreer" => $row['concept_carreer'] ?? null,
                    "concept_month" => $row['concept_month'],
                    "cost" => $row['cost'],
                    "extra" => $row['extra'] ?? null,
                    "amount" => $row['total'],
                    "comments" => $row['comments'],
                    "status" => $row['status']
                ];
            }

            return !empty($response)
                ? ['success' => true, 'data' => $response]
                : ['success' => false, 'message' => 'No se encontraron pagos'];

        } catch (mysqli_sql_exception $e) {

            return [
                "success" => false,
                "message" => "Error al procesar la solicitud"
            ];

        } finally {

            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function checkIfPaymentMade($studentId, $paymentDay)
    {
        try {
            $sql = "SELECT 
                id_student,
                payment_date,
                total,
                CASE 
                    WHEN DAY(payment_date) <= ? THEN 'ON_TIME'
                    ELSE 'EXTEMPORANEO'
                END AS status,
                extra
                FROM students_payments
                WHERE id_student = ?
                    AND isDeleted = 0
                    AND YEAR(payment_date) = YEAR(CURDATE())
                    AND MONTH(payment_date) = MONTH(CURDATE()) AND concept LIKE 'Mensualidad%'
                ORDER BY payment_date ASC
                LIMIT 1;"
            ;
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("si", $paymentDay, $studentId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $response = array(
                    "success" => true,
                    "message" => "El pago ya ha sido realizado este mes",
                    "data" => array(
                        "payment_date" => $row['payment_date'],
                        "total" => $row['total'],
                        "status" => $row['status'],
                        "extra" => $row['extra']
                    )
                );
            } else {
                $response = array("success" => true, "message" => "No se han encontrado pagos para este mes, se podrian aplicar recargos.", "data" => ["status" => "PENDING"]);
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



    public function sendPaymentReceipt($studentId, $paymentId)
    {
        try {

            // Obtener datos del pago
            $sql = "SELECT sp.*, sd.email, sd.nombre AS student_name
                FROM students_payments sp
                JOIN students sd ON sp.id_student = sd.id
                WHERE sp.id = ? 
                AND sp.id_student = ?
                AND sp.isDeleted = 0";

            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                return [
                    "success" => false,
                    "message" => "Error al preparar la consulta"
                ];
            }

            $stmt->bind_param("ii", $paymentId, $studentId);

            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 0) {

                return [
                    "success" => false,
                    "message" => "Pago no encontrado"
                ];
            }

            $paymentData = $result->fetch_assoc();

            $sendReceiptEmail = $this->emailModel->SendPaymentEmail(
                $paymentData['id'],
                $paymentData,
                "https://controlescolar.esmefis.edu.mx/my-receipt.php?id={$paymentData['id']}",
                $paymentData['password'],
                $paymentData['email']
            );

            if (
                isset($sendReceiptEmail['success']) &&
                $sendReceiptEmail['success']
            ) {

                return [
                    "success" => true,
                    "message" => "Comprobante enviado exitosamente"
                ];
            }

            return [
                "success" => false,
                "message" =>
                    "Error al enviar el comprobante: " .
                    ($sendReceiptEmail['message'] ?? 'Error desconocido')
            ];
        } catch (\mysqli_sql_exception $e) {
            return [
                "success" => false,
                "message" => "Error SQL al procesar la solicitud"
            ];
        } catch (\Exception $e) {
            return [
                "success" => false,
                "message" => "Error interno del servidor"
            ];
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }



    public function getReceiptDetailsData($receiptId)
    {
        try {
            $sql = "SELECT students_payments.*, students.nombre AS student_name, students.email AS student_email
                    FROM students_payments
                    JOIN students ON students_payments.id_student = students.id
                    WHERE students_payments.id = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $receiptId);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 0) {
                return array("success" => false, "message" => "Recibo no encontrado");
            }

            return array("success" => true, "data" => $result);

        } catch (mysqli_sql_exception $e) {
            return array("success" => false, "message" => "Error al procesar la solicitud");
        } finally {
            if (isset($stmt)) {
                $stmt->close();
            }
        }
    }

    public function sendPaymentByEmail($studentId, $paymentId)
    {
        return $this->sendPaymentReceipt($studentId, $paymentId);
    }

}
?>