<?php

date_default_timezone_set('America/Mexico_City');

require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\StudentsModel;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\Models\EmailsModel;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
$dotenv->load();

// ─── Conexión ─────────────────────────────────────────────────────────────────
$db = new DBConnection();

// ─── Modelos (directo, sin pasar por Controllers ni auth) ────────────────────
$studentsModel = new StudentsModel($db);
$paymentsModel = new PaymentsModel($db);
$emailModel = new EmailsModel();

// ─── Helpers internos ─────────────────────────────────────────────────────────

/**
 * Verifica si el alumno ya pagó su mensualidad en el mes actual.
 * Reutiliza getPaymentHistory() y filtra por concepto "mensualidad" y mes vigente.
 */
function alumnoYaPago(array $paymentHistory): bool
{
    if (!($paymentHistory['success'] ?? false)) {
        return false;
    }

    if (empty($paymentHistory['data'])) {
        return false;
    }

    $mesActual = (int) date('m');
    $anioActual = (int) date('Y');

    foreach ($paymentHistory['data'] as $pago) {
        if (stripos($pago['concept'], 'mensualidad') === false) {
            continue;
        }

        if (empty($pago['payment_date'])) {
            continue;
        }

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

/**
 * Envía el correo de recordatorio (reutiliza SendPaymentEmail adaptado).
 * Construye el $paymentData mínimo que necesita la plantilla.
 */
function enviarRecordatorio(
    EmailsModel $emailModel,
    string $email,
    string $studentName,
    string $concept,
    float $amount,
    int $diasRestantes,
    int $diaLimitePago
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
    $paymentDate = $diaLimitePago . ' de ' . $meses[(int) date('m')] . ' de ' . date('Y');

    $paymentData = [
        'concept' => ["$concept " . date('Y')],
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

// ─── Lógica principal del cron ────────────────────────────────────────────────
function runPaymentReminders(
    StudentsModel $studentsModel,
    PaymentsModel $paymentsModel,
    EmailsModel $emailModel,
    int $diasParaVencimiento
): void {

    $hoy = new DateTime();
    $fechaObjetivo = (clone $hoy)->modify("+{$diasParaVencimiento} days");

    $diaObjetivo = (int) $fechaObjetivo->format('j');

    echo "[" . $hoy->format('Y-m-d H:i:s') . "] Iniciando recordatorio ({$diasParaVencimiento} días)...\n";
    echo "  Buscando alumnos con vencimiento el {$fechaObjetivo->format('d/m/Y')}...\n";

    $students = $studentsModel->getStudentsForCron();

    if (empty($students) || !isset($students[0]['studentId'])) {
        echo "No se encontraron alumnos o hubo un error al obtenerlos.\n";
        return;
    }

    foreach ($students as $student) {
        if (!($student['success'] ?? false)) {
            continue;
        }

        $studentId = $student['studentId'];
        $studentName = $student['name'];
        $email = $student['email'];

        $paymentInfo = $paymentsModel->verifyMonthlyPayment($studentId);

        if (!($paymentInfo['success'] ?? false) || !isset($paymentInfo['payment_day'])) {
            continue;
        }

        $diaLimitePago = (int) $paymentInfo['payment_day'];
        $montoPagar = (float) $paymentInfo['monthly_amount'];
        $concepto = $paymentInfo['concept'] ?? 'Mensualidad';

        // 3. Comparar día de pago contra la fecha objetivo (respeta cruce de mes)
        if ($diaLimitePago !== $diaObjetivo) {
            continue;
        }

        // 4. Verificar si ya pagó este mes
        $historial = $paymentsModel->getPaymentHistory($studentId);

        if (alumnoYaPago($historial)) {
            echo "  Alumno #{$studentId} ({$studentName}) ya pagó. Se omite.\n";
            continue;
        }

        // 5. Enviar recordatorio
        $resultado = enviarRecordatorio(
            emailModel: $emailModel,
            email: $email,
            studentName: $studentName,
            concept: $concepto,
            amount: $montoPagar,
            diasRestantes: $diasParaVencimiento,
            diaLimitePago: $diaLimitePago
        );

        if ($resultado['success'] ?? false) {
            echo "  ✔ Recordatorio enviado a {$studentName} <{$email}>\n";
        } else {
            echo "  ✘ Error al enviar a {$studentName}: " . ($resultado['message'] ?? 'Desconocido') . "\n";
        }
    }

    echo "Proceso finalizado.\n\n";
}

// ─── Punto de entrada del cron ────────────────────────────────────────────────
// Detecta qué función ejecutar según argumento CLI o ejecuta ambas

$modo = $argv[1] ?? 'all';  // php payment_reminder.php 3 | php payment_reminder.php 1 | php payment_reminder.php all

if ($modo === '3' || $modo === 'all') {
    runPaymentReminders($studentsModel, $paymentsModel, $emailModel, diasParaVencimiento: 3);
}

if ($modo === '1' || $modo === 'all') {
    runPaymentReminders($studentsModel, $paymentsModel, $emailModel, diasParaVencimiento: 1);
}