<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Core\SensitiveActionAuthorizer;
use Vendor\Schoolarsystem\Core\Validation;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\EmailsModel;
use Facturapi\Facturapi;
use Facturapi\Exceptions\Facturapi_Exception;

class PaymentsController
{
    private DBConnection $connection;
    private PaymentsModel $payments;
    private SensitiveActionAuthorizer $sensitiveActions;

    public function __construct()
    {
        $this->connection = DBConnection::getInstance();
        $this->payments = new PaymentsModel($this->connection);
        $this->sensitiveActions = new SensitiveActionAuthorizer();
    }

    public function getPaymentHistory(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->payments->getPaymentHistory($studentId);
    }

    public function getPaymentById(int $studentId, int $paymentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::id($paymentId)) {
            return $error;
        }

        return $this->payments->getPaymentById($studentId, $paymentId);
    }

    public function addPayment(string $paymentData): array
    {
        if ($error = Validation::string($paymentData)) {
            return $error;
        }

        parse_str($paymentData, $paymentDataArray);

        if ($error = Validation::requiredArray($paymentDataArray)) {
            return $error;
        }

        $concept = trim($paymentDataArray['paymentConcept']);
        $concept_subject = !empty($paymentDataArray['subjectConcept']) ? trim($paymentDataArray['subjectConcept']) : NULL;
        $concept_subject_child = !empty($paymentDataArray['childSubjectName']) ? trim($paymentDataArray['childSubjectName']) : NULL;
        $concept_carreer = !empty($paymentDataArray['careerName']) ? trim($paymentDataArray['careerName']) : NULL;
        $concept_month = trim($paymentDataArray['paymentMonth']);
        $date = !empty($paymentDataArray['paymentDate'])
            ? $paymentDataArray['paymentDate']
            : date('Y-m-d');

        $year = date('Y', strtotime($date));
        $concept_month = "$concept_month $year";

        $extra = $paymentDataArray['paymentExtra'] ?? 0;
        $registredBy = $_SESSION['userId'] ?? NULL;

        $isInvoice = ($paymentDataArray['paymentInvoice'] ?? '0') === '1';

        return $this->payments->addPayment(
            $paymentDataArray['studentName'],
            $date,
            $paymentDataArray['paymentMethod'],
            $isInvoice,
            $concept,
            $concept_subject,
            $concept_subject_child,
            $concept_carreer,
            preg_replace('/\s+/', ' ', trim($concept_month)),
            $paymentDataArray['paymentPrice'],
            $extra,
            $paymentDataArray['paymentTotal'],
            $paymentDataArray['paymentComments'] ?? '',
            $registredBy
        );
    }

    public function updatePayment(string $paymentData): array
    {
        if ($error = Validation::string($paymentData)) {
            return $error;
        }

        parse_str($paymentData, $paymentDataArray);

        if ($error = Validation::requiredArray($paymentDataArray)) {
            return $error;
        }

        return $this->payments->updatePayment(
            $paymentDataArray['idPayment'],
            $paymentDataArray['paymentPrice'],
            $paymentDataArray['paymentExtra'] ?? 0.00,
            $paymentDataArray['paymentTotal'],
            $paymentDataArray['paymentMethod'],
            $paymentDataArray['paymentComments'] ?? ''
        );
    }

    public function deletePaymentById(int $paymentId, ?string $password = null): array
    {
        if ($error = Validation::id($paymentId)) {
            return $error;
        }

        $authorization = $this->sensitiveActions->authorize($password);

        if (!$authorization['success']) {
            return $authorization;
        }

        return $this->payments->softdeletePaymentById($paymentId);
    }

    public function cancelPaymentById(int $paymentId, string $comments): array
    {
        if ($error = Validation::id($paymentId)) {
            return $error;
        }

        if ($error = Validation::string($comments)) {
            return $error;
        }

        return $this->payments->cancelPaymentById($paymentId, $comments);
    }

    // ─── Recordatorios de pago (cron) ─────────────────────────────────────────
    // Sin auth::check(): se ejecuta desde CLI, protegido por el guard del cronjob.
    // El echo es intencional: cPanel captura stdout en el correo del cron.

    private function alumnoYaPago(array $paymentHistory): bool
    {
        if (!($paymentHistory['success'] ?? false) || empty($paymentHistory['data'])) {
            return false;
        }

        $mesActual = (int) date('m');
        $anioActual = (int) date('Y');

        foreach ($paymentHistory['data'] as $pago) {
            if (stripos($pago['concept'], 'mensualidad') === false)
                continue;
            if (empty($pago['payment_date']))
                continue;

            $fechaPago = new \DateTime($pago['payment_date']);
            if (
                (int) $fechaPago->format('m') === $mesActual &&
                (int) $fechaPago->format('Y') === $anioActual &&
                $pago['status'] !== 'cancelled'
            ) {
                return true;
            }
        }

        return false;
    }

    private function enviarRecordatorio(
        EmailsModel $emailModel,
        string $email,
        string $studentName,
        string $concept,
        float $amount,
        int $diasRestantes,
        int $diaLimitePago,
        \DateTime $fechaObjetivo
    ): array {
        $asunto = $diasRestantes === 1
            ? '⚠️ Último día para pagar tu mensualidad – ESMEFIS'
            : "Recordatorio: tu mensualidad vence en {$diasRestantes} días – ESMEFIS";

        $meses = [
            1 => 'enero',
            2 => 'febrero',
            3 => 'marzo',
            4 => 'abril',
            5 => 'mayo',
            6 => 'junio',
            7 => 'julio',
            8 => 'agosto',
            9 => 'septiembre',
            10 => 'octubre',
            11 => 'noviembre',
            12 => 'diciembre'
        ];

        $mesVencimiento = (int) $fechaObjetivo->format('n');
        $anioVencimiento = (int) $fechaObjetivo->format('Y');

        $paymentDate = $diaLimitePago . ' de ' . $meses[$mesVencimiento] . ' de ' . $anioVencimiento;

        $paymentData = [
            'concept' => ["{$concept} " . $meses[$mesVencimiento] . " {$anioVencimiento}"],
            'total' => [$amount],
            'email' => $email,
        ];

        return $emailModel->SendReminderEmail(
            studentName: $studentName,
            email: $email,
            subject: $asunto,
            paymentData: $paymentData,
            diasRestantes: $diasRestantes,
            paymentDate: $paymentDate
        );
    }

    public function runPaymentReminders(
        StudentsModel $studentsModel,
        EmailsModel $emailModel,
        int $diasParaVencimiento
    ): void {
        $hoy = new \DateTime();
        $fechaObjetivo = (clone $hoy)->modify("+{$diasParaVencimiento} days");
        $diaObjetivo = (int) $fechaObjetivo->format('j');

        echo "[" . $hoy->format('Y-m-d H:i:s') . "] Recordatorio {$diasParaVencimiento} día(s) antes — vencimiento el {$fechaObjetivo->format('d/m/Y')}...\n";

        $students = $studentsModel->getStudentsForCron();

        if (empty($students) || !isset($students[0]['studentId'])) {
            echo "  Sin alumnos registrados.\n\n";
            return;
        }

        foreach ($students as $student) {
            if (!($student['success'] ?? false))
                continue;

            $studentId = $student['studentId'];
            $studentName = $student['name'];
            $email = $student['email'];

            $paymentInfo = $this->payments->verifyMonthlyPayment($studentId);

            if (!($paymentInfo['success'] ?? false) || !isset($paymentInfo['payment_day']))
                continue;

            $diaLimitePago = (int) $paymentInfo['payment_day'];
            $montoPagar = (float) $paymentInfo['monthly_amount'];
            $concepto = $paymentInfo['concept'] ?? 'Mensualidad';

            if ($diaLimitePago !== $diaObjetivo)
                continue;

            $historial = $this->payments->getPaymentHistory($studentId);

            if ($this->alumnoYaPago($historial)) {
                echo "  Alumno #{$studentId} ({$studentName}) ya pagó. Se omite.\n";
                continue;
            }

            $resultado = $this->enviarRecordatorio(
                emailModel: $emailModel,
                email: $email,
                studentName: $studentName,
                concept: $concepto,
                amount: $montoPagar,
                diasRestantes: $diasParaVencimiento,
                diaLimitePago: $diaLimitePago,
                fechaObjetivo: $fechaObjetivo
            );

            if ($resultado['success'] ?? false) {
                echo "  ✔ Enviado a {$studentName} <{$email}>\n";
            } else {
                echo "  ✘ Error con {$studentName}: " . ($resultado['message'] ?? 'Desconocido') . "\n";
            }
        }

        echo "  Finalizado.\n\n";
    }

    public function verifyPassword(?string $password = null): array
    {
        return $this->sensitiveActions->authorize($password);
    }

    public function verifyTaxData(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->payments->verifyTaxData($studentId);
    }

    public function getFacturApiData(string $clientId): array|object
    {
        if ($error = Validation::string($clientId)) {
            return $error;
        }

        try {
            $facturapi = new Facturapi($_ENV['FACTURAPI_KEY']);
            $response = $facturapi->Customers->retrieve($clientId);
        } catch (Facturapi_Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage());
        }
        return $response;
    }

    public function addFactuarapi(
        string $clientId,
        int $taxSystem,
        array $invoiceData
    ): array {
        $use = " ";
        $validTaxSystems = [605, 606, 608, 611, 612, 614, 607, 615, 625];
        $generalTaxSystems = [601, 603, 620, 621, 622, 623, 624, 626];
        $extraTaxSystems = [610, 616];

        if (in_array($taxSystem, $validTaxSystems, true)) {
            $use = "D10";
        } elseif (in_array($taxSystem, $extraTaxSystems, true)) {
            $use = "S01";
        } elseif (in_array($taxSystem, $generalTaxSystems, true)) {
            $use = "G03";
        }

        try {
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
        } catch (Facturapi_Exception $e) {
            $response = array("success" => false, "message" => $e->getMessage());
        } finally {
            return $response;
        }
    }

    public function getStudentsPayMount(): array
    {
        return $this->payments->getStudentsPayMount();
    }

    public function savePaymentDays(array $data): array
    {
        if ($error = Validation::requiredArray($data)) {
            return $error;
        }

        $paymentData = $data['data'] ?? $data;

        if (!is_array($paymentData)) {
            return [
                "success" => false,
                "message" => "Los datos de pago son inválidos."
            ];
        }

        return $this->payments->savePaymentDays($paymentData);
    }

    public function setStudentPayMount(int $studentId, float $amount): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->payments->setStudentPayMount($studentId, $amount);
    }

    public function verifyMonthlyPayment(int $studentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->payments->verifyMonthlyPayment($studentId);
    }

    public function checkIfPaymentMade(
        int $studentId,
        int $paymentDay
    ): array {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        return $this->payments->checkIfPaymentMade($studentId, $paymentDay);
    }

    public function sendPaymentReceipt(int $studentId, int $paymentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::id($paymentId)) {
            return $error;
        }

        return $this->payments->sendPaymentReceipt($studentId, $paymentId);
    }

    public function sendPaymentByEmail(int $studentId, int $paymentId): array
    {
        if ($error = Validation::id($studentId)) {
            return $error;
        }

        if ($error = Validation::id($paymentId)) {
            return $error;
        }

        return $this->payments->sendPaymentByEmail($studentId, $paymentId);
    }
}
