<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\Core\DatabaseHelper;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Helpers\RandomPasswords;

class PaymentsModel
{
    private $connection;
    private FacturapiModel $facturapiModel;
    private StudentsModel $studentsModel;
    private EmailsModel $emailModel;
    private RandomPasswords $passwordsHelper;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection->getConnection();
        $this->facturapiModel = new FacturapiModel($dbConnection);
        $this->studentsModel = new StudentsModel($dbConnection);
        $this->emailModel = new EmailsModel();
        $this->passwordsHelper = new RandomPasswords();
    }

    public function getPaymentHistory(int $studentId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
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
                    total AS amount,
                    comments,
                    status
                FROM students_payments
                WHERE id_student = ? AND isDeleted = 0
                ORDER BY payment_date DESC;
            ",
            "i",
            [$studentId]
        );
    }

    public function getPaymentById(int $studentId, int $paymentId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
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
                    total AS amount,
                    comments,
                    status
                FROM students_payments
                WHERE id_student = ? AND id = ? AND isDeleted = 0;
            ",
            "ii",
            [$studentId, $paymentId]
        );
    }

    public function addPayment(
        int $studentId,
        string $date,
        int $paymentMethod,
        bool $isInvoice,
        string $concept,
        ?string $conceptSubject,
        ?string $conceptSubjectChild,
        ?string $conceptCareer,
        string $conceptMonth,
        float $cost,
        float $extra,
        float $total,
        string $comments,
        ?int $registeredBy
    ): array {
        $password = $this->passwordsHelper->generateRandomPassword(12);
        $status = $isInvoice ? 'pending' : 'confirmed';

        $payment = DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO students_payments (
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
                    registred_by,
                    password,
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?);
            ",
            "isiisssssdddsiss",
            [
                $studentId,
                $date,
                $paymentMethod,
                (int) $isInvoice,
                $concept,
                $conceptSubject,
                $conceptSubjectChild,
                $conceptCareer,
                $conceptMonth,
                $cost,
                $extra,
                $total,
                $comments,
                $registeredBy,
                $password,
                $status
            ]
        );

        if (!$payment["success"]) {
            return $payment;
        }

        $paymentId = (int) $payment["insertedId"];

        if (!$isInvoice) {
            return [
                "success" => true,
                "message" => "Pago registrado exitosamente.",
                "paymentId" => $paymentId
            ];
        }

        $student = $this->studentsModel->getStudentById($studentId);
        $studentEmail = $student["data"]["email"] ?? null;

        $invoiceResponse = $this->facturapiModel->createReceipt(
            [
                "date" => $date,
                "paymentForm" => $paymentMethod,
                "products" => [
                    [
                        "product" => $concept,
                        "unitPrice" => $cost + $extra,
                        "quantity" => 1,
                        "subTotal" => $total
                    ]
                ],
                "email" => $studentEmail
            ],
            $paymentId
        );

        if (!$invoiceResponse["success"]) {
            return [
                "success" => false,
                "message" => "Pago registrado, pero no fue posible generar la factura: "
                    . ($invoiceResponse["message"] ?? "Error desconocido."),
                "paymentId" => $paymentId
            ];
        }

        return [
            "success" => true,
            "message" => "Pago y factura registrados exitosamente.",
            "paymentId" => $paymentId,
            "invoiceId" => $invoiceResponse["receipt"] ?? null
        ];
    }

    public function updatePayment(
        int $paymentId,
        float $cost,
        float $extra,
        float $total,
        int $method,
        string $comments
    ): array {
        $currentPayment = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT cost, extra, total, payment_method, comments, status
                FROM students_payments
                WHERE id = ? AND isDeleted = 0;
            ",
            "i",
            [$paymentId]
        );

        if (!$currentPayment["success"]) {
            return $currentPayment;
        }

        if ($currentPayment["data"]["status"] === 'cancelled') {
            return [
                "success" => false,
                "message" => "No se puede modificar un pago cancelado."
            ];
        }

        $newData = [
            "cost" => $cost,
            "extra" => $extra,
            "total" => $total,
            "payment_method" => $method,
            "comments" => $comments
        ];

        $currentData = $currentPayment["data"];
        unset($currentData["status"]);

        if ($currentData == $newData) {
            return [
                "success" => false,
                "message" => "No se detectaron cambios para guardar."
            ];
        }

        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE students_payments
                SET
                    cost = ?,
                    extra = ?,
                    total = ?,
                    payment_method = ?,
                    comments = ?
                WHERE id = ?
                    AND status != 'cancelled'
                    AND isDeleted = 0;
            ",
            "dddisi",
            [$cost, $extra, $total, $method, $comments, $paymentId]
        );
    }

    public function softdeletePaymentById(int $paymentId): array
    {
        return DatabaseHelper::update(
            $this->connection,
            "UPDATE students_payments SET isDeleted = 1 WHERE id = ?;",
            "i",
            [$paymentId]
        );
    }

    public function cancelPaymentById(int $paymentId, string $comments): array
    {
        $payment = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT status
                FROM students_payments
                WHERE id = ? AND isDeleted = 0;
            ",
            "i",
            [$paymentId]
        );

        if (!$payment["success"]) {
            return $payment;
        }

        if ($payment["data"]["status"] === 'cancelled') {
            return [
                "success" => false,
                "message" => "El pago ya está cancelado."
            ];
        }

        return DatabaseHelper::update(
            $this->connection,
            "
                UPDATE students_payments
                SET status = 'cancelled', comments = ?
                WHERE id = ? AND status != 'cancelled' AND isDeleted = 0;
            ",
            "si",
            [$comments, $paymentId]
        );
    }

    public function verifyTaxData(int $studentId): array
    {
        return DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT *
                FROM invoice_data
                WHERE id_student = ?;
            ",
            "i",
            [$studentId]
        );
    }

    public function getStudentsPayMount(): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    s.id,
                    s.nombre AS name,
                    spa.monthly_amount AS amount
                FROM students s
                LEFT JOIN students_payments_amounts spa
                    ON s.id = spa.id_student
                ORDER BY s.id;
            "
        );
    }

    public function setStudentPayMount(int $studentId, float $amount): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO students_payments_amounts (id_student, monthly_amount)
                VALUES (?, ?)
                ON DUPLICATE KEY UPDATE monthly_amount = ?;
            ",
            "idd",
            [$studentId, $amount, $amount]
        );
    }

    public function verifyMonthlyPayment(int $studentId): array
    {
        $payment = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT amount, payment_day, concept
                FROM payments_dates
                WHERE id_student = ?;
            ",
            "i",
            [$studentId]
        );

        if (!$payment["success"]) {
            return $payment;
        }

        return [
            "success" => true,
            "data" => [
                "monthly_amount" => $payment["data"]["amount"],
                "payment_day" => $payment["data"]["payment_day"],
                "concept" => $payment["data"]["concept"],
            ],
            "message" => $payment["message"]
        ];
    }

    public function savePaymentDays(array $paymentData): array
    {
        return DatabaseHelper::insert(
            $this->connection,
            "
                INSERT INTO payments_dates (id_student, payment_day, concept, amount)
                VALUES (?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    payment_day = ?,
                    concept = ?,
                    amount = ?;
            ",
            "iisdisd",
            [
                $paymentData["studentId"],
                $paymentData["paymentDay"],
                $paymentData["paymentConcept"],
                $paymentData["paymentAmount"],
                $paymentData["paymentDay"],
                $paymentData["paymentConcept"],
                $paymentData["paymentAmount"]
            ]
        );
    }

    public function checkIfPaymentMade(
        int $studentId,
        int $paymentDay
    ): array {
        $payment = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT
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
                    AND MONTH(payment_date) = MONTH(CURDATE())
                    AND concept LIKE 'Mensualidad%'
                ORDER BY payment_date ASC
                LIMIT 1;
            ",
            "ii",
            [$paymentDay, $studentId]
        );

        if (!$payment["success"]) {
            if (
                ($payment["message"] ?? '') !==
                "No se encontró el registro solicitado."
            ) {
                return $payment;
            }

            return [
                "success" => true,
                "message" => "No se encontraron pagos para este mes; podrían aplicarse recargos.",
                "data" => ["status" => "PENDING"]
            ];
        }

        return [
            "success" => true,
            "message" => "El pago ya fue realizado este mes.",
            "data" => $payment["data"]
        ];
    }

    public function sendPaymentReceipt(int $studentId, int $paymentId): array
    {
        $payment = DatabaseHelper::selectOne(
            $this->connection,
            "
                SELECT sp.*, s.email, s.nombre AS student_name
                FROM students_payments sp
                INNER JOIN students s ON sp.id_student = s.id
                WHERE sp.id = ? AND sp.id_student = ? AND sp.isDeleted = 0;
            ",
            "ii",
            [$paymentId, $studentId]
        );

        if (!$payment["success"]) {
            return $payment;
        }

        $paymentData = $payment["data"];
        $emailResponse = $this->emailModel->SendPaymentEmail(
            $paymentData["id"],
            $paymentData,
            "https://controlescolar.esmefis.edu.mx/my-receipt.php?id={$paymentData['id']}",
            $paymentData["password"],
            $paymentData["email"]
        );

        if (!($emailResponse["success"] ?? false)) {
            return [
                "success" => false,
                "message" => "Error al enviar el comprobante: "
                    . ($emailResponse["message"] ?? "Error desconocido.")
            ];
        }

        return [
            "success" => true,
            "message" => "Comprobante enviado exitosamente."
        ];
    }

    public function getReceiptDetailsData(int $receiptId): array
    {
        return DatabaseHelper::selectAll(
            $this->connection,
            "
                SELECT
                    sp.*,
                    s.nombre AS student_name,
                    s.email AS student_email
                FROM students_payments sp
                INNER JOIN students s ON sp.id_student = s.id
                WHERE sp.id = ?;
            ",
            "i",
            [$receiptId]
        );
    }

    public function sendPaymentByEmail(int $studentId, int $paymentId): array
    {
        return $this->sendPaymentReceipt($studentId, $paymentId);
    }
}
