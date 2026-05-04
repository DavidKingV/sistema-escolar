<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Schoolarsystem\loadEnv;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\TwigFunction;

class EmailsModel{
    private $mail;

    public function __construct(){
        $this->mail = new PHPMailer(true);
    }

    public function SendEventEmail($eventId, $eventDateTime, $eventType, $email){
        try {
            $loader = new FilesystemLoader(__DIR__ . '/../../views'); // Directorio donde están tus plantillas
            $twig = new Environment($loader);

            $htmlContent = $twig->render('eventDetails.html', [
                'EVENT_ID' => $eventId,
                'EVENT_DATE' => $eventDateTime,
                'EVENT_TYPE' => $eventType,
            ]);

            //Server settings
            $this->mail->SMTPDebug = 0;                      // Enable verbose debug output
            $this->mail->isSMTP();                                            // Send using SMTP
            $this->Debugoutput = 'html';
            $this->mail->Host       = $_ENV['EMAIL_HOST'];                     // Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $this->mail->Username   = $_ENV['EMAIL_USER'];                     // SMTP username
            $this->mail->Password   = $_ENV['EMAIL_PASS'];                               // SMTP password
            $this->mail->SMTPSecure = 'TLS';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $this->mail->Port       = $_ENV['EMAIL_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            $this->mail->setFrom($_ENV['EMAIL_USER'], 'Clínica UNIF');
            $this->mail->addAddress($email);     // Add a recipient

            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Body    = $htmlContent;

            if (!$this->mail->send()) {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el mensaje. Error: ' . $this->mail->ErrorInfo,
                ];
            }

            return [
                'success' => true,
                'message' => 'Correo enviado exitosamente.',
            ];

        }catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo
            ];
        }
    }

    public function SendPaymentEmail($paymentId, $paymentData, $url, $pdfPassword, $email){
        try {
            $loader = new FilesystemLoader(__DIR__ . '/../../views/emails'); // Directorio donde están tus plantillas
            $twig = new Environment($loader);

            // Definir una función personalizada para formatear números como moneda
            $products = is_array($paymentData['concept']) 
                ? $paymentData['concept'] 
                : [$paymentData['concept']];

            $subtotals = is_array($paymentData['total']) 
                ? $paymentData['total'] 
                : [$paymentData['total']];

            foreach ($products as $i => $producto) {
                $productos[] = [
                    'product'  => $producto,
                    'subTotal' => $subtotals[$i] ?? null, // usamos ?? por si no existe índice
                ];
            }

            $htmlContent = $twig->render('payment.html', [
                'PAYMENT_ID' => $paymentId,
                'PRODUCTS'   => $productos,
                'TOTAL'      => $paymentData['total'],
                'URL'        => $url,
                'PASSWORD' => $pdfPassword,
            ]);

            //Server settings
            $this->mail->SMTPDebug = 0;                      // Enable verbose debug output
            $this->mail->isSMTP();                                            // Send using SMTP
            $this->Debugoutput = 'html';
            $this->mail->Host       = $_ENV['EMAIL_HOST'];                     // Set the SMTP server to send through
            $this->mail->SMTPAuth   = true;                                   // Enable SMTP authentication
            $this->mail->Username   = $_ENV['EMAIL_USER'];                     // SMTP username
            $this->mail->Password   = $_ENV['EMAIL_PASS'];                               // SMTP password
            $this->mail->SMTPSecure = 'TLS';         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
            $this->mail->Port       = $_ENV['EMAIL_PORT'];                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

            $this->mail->setFrom($_ENV['EMAIL_USER'], 'ESMEFIS Centro Universitario');
            $this->mail->addAddress($email);     // Add a recipient

            $this->mail->isHTML(true);                                  // Set email format to HTML
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Subject = 'Recibo de pago ESMEFIS';
            $this->mail->Body    = $htmlContent;

            if (!$this->mail->send()) {
                return [
                    'success' => false,
                    'message' => 'Error al enviar el mensaje. Error: ' . $this->mail->ErrorInfo,
                ];
            }

            return [
                'success' => true,
                'message' => 'Correo enviado exitosamente.',
            ];

        }catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Message could not be sent. Mailer Error: ' . $this->mail->ErrorInfo
            ];
        }
    }
}