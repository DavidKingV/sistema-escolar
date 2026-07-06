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
use Vendor\Schoolarsystem\Models\EmailsModel;
use Vendor\Schoolarsystem\Controllers\PaymentsController;

$hoy = new DateTime();
echo "========================================\n";
echo "CRON RECORDATORIO PAGOS — " . $hoy->format('d/m/Y H:i:s') . "\n";
echo "========================================\n\n";

try {
    $dotenv = Dotenv::createImmutable(dirname(__DIR__, 2));
    $dotenv->load();

    $db = new DBConnection();
    $studentsModel = new StudentsModel($db);
    $emailModel = new EmailsModel();
    $controller = new PaymentsController($db);

    // Siempre corre ambos: 3 días antes y último día.
    $controller->runPaymentReminders($studentsModel, $emailModel, diasParaVencimiento: 3);
    $controller->runPaymentReminders($studentsModel, $emailModel, diasParaVencimiento: 1);
} catch (\Throwable $e) {
    // El cron de cPanel captura stderr/stdout: el fallo llega por correo sin tumbar el resto.
    fwrite(STDERR, "FATAL: {$e->getMessage()}\n{$e->getFile()}:{$e->getLine()}\n");
    exit(1);
}
