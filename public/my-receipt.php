<?php
require __DIR__ . '/../backend/vendor/autoload.php';

use Vendor\Schoolarsystem\DBConnection;
use Vendor\Schoolarsystem\Models\PaymentsModel;
use Vendor\Schoolarsystem\Helpers\NumbersToLetters;

$payments = new PaymentsModel(new DBConnection());
$toLetters = new NumbersToLetters();

// Validar y sanitizar el parámetro 'id'
$receiptId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$receiptId) {
    die("ID de recibo inválido");
}

$result = $payments->getReceiptDetailsData($receiptId);

if (!$result['success']) {
    die("No se encontraron datos para el recibo.");
}

$dataResult = $result['data'];
$rows = [];

// Almacenar todos los registros en un arreglo
while ($row = $dataResult->fetch_assoc()) {
    $rows[] = $row;
}

// Verificar que se haya obtenido al menos un registro
if (empty($rows)) {
    die("El recibo no tiene datos de detalles.");
}

// Usar la primera fila para datos generales (si estos se encuentran en cada registro)
$headerData = $rows[0];

// Preparar formatos y datos extra
$formattedTotal = "$" . number_format($headerData['total'], 2);
$letterTotal = $toLetters->convertirEurosEnLetras($headerData['total']);
$formattedDate = date("d-m-Y", strtotime($headerData['payment_date']));

// Armar la plantilla HTML del PDF
$pdf = '<!DOCTYPE html>
<html lang="es">
  <head>
    <meta charset="utf-8">
    <title>Recibo UNIF</title>
  </head>
  <body>
    <header class="clearfix">
      <div id="logo">
        <img src="assets/img/escudo.png" width="80">
      </div>
      <h1>RECIBO ESMEFIS</h1>
      <div id="company" class="clearfix">
        <div>ESMEFIS Centro Universitario</div>
        <div>Canal de Miramontes #1776,<br /> Campestre Churubusco 04200, Coyoacán CDMX</div>
        <div>55 5336-9698</div>
        <div><a href="mailto:informes@esmefis.edu.mx">informes@esmefis.edu.mx</a></div>
      </div>
      <div id="project">
        <div><span>NO. RECIBO: </span> ' . htmlspecialchars($receiptId, ENT_QUOTES, 'UTF-8') . '</div>
        <div><span>PACIENTE: </span> ' . $headerData['student_name'] . '</div>
        <div><span>FECHA DE CREACIÓN: </span> ' . $formattedDate . '</div>
        <div><span>FECHA DE IMPRESIÓN: </span> ' . date("d-m-Y") . '</div>
        <div><span>METODO DE PAGO: </span> ' . $headerData['payment_method'] . '/' . $headerData['tipo'] . '</div>
      </div>
    </header>
    <main>
      <table>
        <thead>
          <tr>
            <th class="desc">DESCRIPCIÓN</th>
            <th>PRECIO/U</th>
            <th>CARGO EXTRA</th>
            <th>IMPORTE</th>
          </tr>
        </thead>
        <tbody>';

// Recorrer el arreglo de filas para generar cada línea del detalle
foreach ($rows as $row) {
    $pdf .= '<tr>
                <td class="desc">' . $row['concept'] . '</td>
                <td class="unit">' . $row['cost'] . '</td>
                <td class="unit">' . $row['extra'] . '</td>
                <td class="qty">' . $row['total'] . '</td>
              </tr>';
}

$pdf .= '<tr>
            <td colspan="3" class="grand total">TOTAL</td>
            <td class="grand total">' . $formattedTotal . '</td>
          </tr>
          <tr>
            <td colspan="4" class="grand total"> (' . $letterTotal . ') </td>
          </tr>
        </tbody>
      </table>
      <div id="notices">
        <div></div>
      </div>
    </main>
    <footer>
      ESMEFIS Centro Universitario - Recibo generado por el sistema de control escolar 
    </footer>
  </body>
</html>';

// Generación del PDF usando mPDF
$mpdf = new \Mpdf\Mpdf();

/*if (isset($headerData['status']) && strtolower($headerData['status']) === 'canceled') {
  $watermark = new \Mpdf\WatermarkText(
    'CANCELADO',
    72,
    45,
    '#000000',
    0.1
);
$mpdf->SetWatermarkText($watermark);
$mpdf->showWatermarkText = true;
}*/

$mpdf->SetProtection(['print', 'copy'], $headerData['password'], 'Sistemas123@');
$css = file_get_contents("assets/css/receiptPdf.css");
$mpdf->writeHtml($css, 1);
$mpdf->writeHtml($pdf);
$mpdf->output();
?>
