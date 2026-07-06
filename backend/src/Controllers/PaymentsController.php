<?php
namespace Vendor\Schoolarsystem\Controllers;

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\EmailsModel;
use Vendor\Schoolarsystem\auth;
use Facturapi\Facturapi;
use Facturapi\Exceptions\Facturapi_Exception;
require_once(__DIR__ . '/../../login/index.php');

class PaymentsController
{
    private $connection;
    private $payments;
    private $loginControl;

    public function __construct(DBConnection $dbConnection)
    {
        $this->connection = $dbConnection;
        $this->payments = new PaymentsModel($dbConnection);
        $this->loginControl = new \LoginControl($dbConnection);
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
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
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

    public function verifyPassword($password)
    {
        $verifySession = auth::check();

        if (!$verifySession['success']) {
            return [
                "success" => false,
                "message" => "Sesión expirada"
            ];
        }

        $userId = $_SESSION['userId'];

        $isValidPassword = $this->loginControl
            ->verifyUserPassword($userId, $password);

        if (!$isValidPassword) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta"
            ];
        }

        return [
            "success" => true,
            "message" => "Contraseña verificada"
        ];
    }

    public function verifyTaxData($studentId)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->verifyTaxData($studentId);
    }

    public function getFacturApiData($clientId)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
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

    public function addFactuarapi($clientId, $taxSystem, $invoiceData)
    {
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

    public function addPayment($paymentDataArray)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
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

        $this->convertDate($date);

        $paymentInvoice = $paymentDataArray['paymentInvoice'] === '1';
        $isInvoice = $paymentInvoice ? true : false;

        return $this->payments->addPayment(
            $paymentDataArray['studentName'],
            $date,
            $paymentDataArray['paymentMethod'],
            $isInvoice,
            $concept,
            $concept_subject,
            $concept_subject_child,
            $concept_carreer,
            $concept_month = preg_replace('/\s+/', ' ', trim($concept_month)),
            $paymentDataArray['paymentPrice'],
            $extra,
            $paymentDataArray['paymentTotal'],
            $paymentDataArray['paymentComments'] ?? '',
            $registredBy
        );
    }

    public function updatePayment($paymentDataArray)
    {
        $verifySession = auth::check();

        if (!$verifySession['success']) {
            return [
                "success" => false,
                "message" => "Sesión expirada"
            ];
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

    public function deletePayment($paymentId, $password)
    {
        $verifySession = auth::check();

        if (!$verifySession['success']) {
            return [
                "success" => false,
                "message" => "Sesión expirada"
            ];
        }

        $userId = $_SESSION['userId'];

        // validar password
        $isValidPassword = $this->loginControl
            ->verifyUserPassword($userId, $password);

        if (!$isValidPassword) {
            return [
                "success" => false,
                "message" => "Contraseña incorrecta"
            ];
        }

        return $this->payments->softDeletePayment($paymentId);
    }

    public function cancelPayment($paymentId, $comments)
    {
        $verifySession = auth::check();

        if (!$verifySession['success']) {
            return [
                "success" => false,
                "message" => "Sesión expirada"
            ];
        }

        return $this->payments->cancelPayment($paymentId, $comments);
    }

    private function convertDate($date)
    {
        $year = date('Y', strtotime($date));
        $month = date('m', strtotime($date));
        return array('year' => $year, 'month' => $month);
    }

    public function getStudentsPayMount()
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->getStudentsPayMount();
    }

    public function savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->savePaymentDays($studentId, $paymentDay, $paymentConcept, $paymentAmount);
    }

    public function setStudentPayMount($studentId, $amount)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->setStudentPayMount($studentId, $amount);
    }

    public function verifyMonthlyPayment($studentId)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->verifyMonthlyPayment($studentId);
    }

    public function getPaymentHistory($studentId, $paymentId = null)
    {
        $verifySession = auth::check();

        if (!$verifySession['success']) {
            return [
                "success" => false,
                "message" => "No se ha iniciado sesión o la sesión ha expirado"
            ];
        }

        return $this->payments->getPaymentHistory($studentId, $paymentId);
    }

    public function checkIfPaymentMade($studentId, $paymentDay)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->checkIfPaymentMade($studentId, $paymentDay);

    }

    public function sendPaymentReceipt($studentId, $paymentId)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->sendPaymentReceipt($studentId, $paymentId);
    }

    public function sendPaymentByEmail($studentId, $paymentId)
    {
        $verifySession = auth::check();
        if (!$verifySession['success']) {
            return array("success" => false, "message" => "No se ha iniciado sesión o la sesión ha expirado");
        }
        return $this->payments->sendPaymentByEmail($studentId, $paymentId);
    }
}
