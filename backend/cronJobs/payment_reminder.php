<?php

// Bloquear ejecución si no es CLI
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    exit('Acceso no permitido.');
}

date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\Models\EmailsModel;

$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

$db = new DBConnection();
$studentsModel = new StudentsModel($db);
$paymentsModel = new PaymentsModel($db);
$emailModel = new EmailsModel();

function alumnoYaPago(array $paymentHistory): bool
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

        $fechaPago = new DateTime($pago['payment_date']);
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

function enviarRecordatorio(
    EmailsModel $emailModel,
    string $email,
    string $studentName,
    string $concept,
    float $amount,
    int $diasRestantes,
    int $diaLimitePago,
    DateTime $fechaObjetivo
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

    // Mes y año del vencimiento real
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

function runPaymentReminders(
    StudentsModel $studentsModel,
    PaymentsModel $paymentsModel,
    EmailsModel $emailModel,
    int $diasParaVencimiento
): void {

    $hoy = new DateTime();
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

        $paymentInfo = $paymentsModel->verifyMonthlyPayment($studentId);

        if (!($paymentInfo['success'] ?? false) || !isset($paymentInfo['payment_day']))
            continue;

        $diaLimitePago = (int) $paymentInfo['payment_day'];
        $montoPagar = (float) $paymentInfo['monthly_amount'];
        $concepto = $paymentInfo['concept'] ?? 'Mensualidad';

        if ($diaLimitePago !== $diaObjetivo)
            continue;

        $historial = $paymentsModel->getPaymentHistory($studentId);

        if (alumnoYaPago($historial)) {
            echo "  Alumno #{$studentId} ({$studentName}) ya pagó. Se omite.\n";
            continue;
        }

        $resultado = enviarRecordatorio(
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

// ─── Ejecución automática — siempre corre ambos ───────────────────────────────
$hoy = new DateTime();
echo "========================================\n";
echo "CRON RECORDATORIO PAGOS — " . $hoy->format('d/m/Y H:i:s') . "\n";
echo "========================================\n\n";

runPaymentReminders($studentsModel, $paymentsModel, $emailModel, diasParaVencimiento: 3);
runPaymentReminders($studentsModel, $paymentsModel, $emailModel, diasParaVencimiento: 1);