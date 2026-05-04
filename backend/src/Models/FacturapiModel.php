<?php
namespace Vendor\Schoolarsystem\Models;

use Vendor\Unif\GetEnv;
use Facturapi\Facturapi;
use Facturapi\Http\BaseClient;
use Facturapi\Exceptions\Facturapi_Exception;

class FacturApiModel{
    private $facturapi;

    /* ==== MAPEOS COMO ESTÁTICOS PRIVADOS ==== */
    private static array $factorMapping = [
        "Mensualidad" => "Exento",
        "Inscripción" => "Exento",        
    ];

    private static array $unitKeyMapping = [
        "Mensualidad" => "E48",
        "Inscripción" => "E48",
    ];

    private static array $unitNameMapping = [
        "Mensualidad" => "Servicio",
        "Inscripción" => "Servicio",
    ];

    private static array $productKeyMapping = [
        "Mensualidad" => "86121800",
        "Inscripción" => "86121800",
    ];

    private static array $paymentMapping = [
        '01'           => '01', // Efectivo
        '04' => '04', // Tarjeta de crédito
        '28'  => '28', // Tarjeta de débito
        '03'      => '03', // Transferencia electrónica
    ];

    public function __construct(\Vendor\Schoolarsystem\DBConnection $dbConnection) {
        // Obtener la API KEY desde variable de entorno o configuración
        $apiKey = $_ENV['FACTURAPI_KEY'] ?? null;
        if (!$apiKey) {
            // Si tienes un método para cargar variables de entorno personalizado, úsalo aquí
            if (class_exists('Vendor\\Unif\\GetEnv')) {
                $apiKey = \Vendor\Unif\GetEnv::get('FACTURAPI_KEY');
            }
        }
        $this->facturapi = new Facturapi($apiKey);
    }

    public function createInvoice($invoiceData, $invoiceId) {
        $facturApiClientId = $invoiceData['facturapiId'];

        $totalProducts = count($invoiceData['product']);

        $payment_form = isset(self::$paymentMapping[$invoiceData['paymentForm']]) ? self::$paymentMapping[$invoiceData['paymentForm']] : "01";

        $customer = $this->getCustomer($facturApiClientId);
        $tax_system = $customer[0]['tax_system'];

        if($tax_system === '616'){
            $use = 'S01';
        }elseif (in_array($tax_system, ['605','606','608','611','612','614','607','615','625'])) {
            $use = 'D01';
        }else{
            $use = 'G03';
        }

        $items = [];
        for ($i = 0; $i < $totalProducts; $i++) {
            $productName = trim($invoiceData['product'][$i]);
            $productQuantity = $invoiceData['quantity'][$i];
            $productUnitPrice = $invoiceData['unitPrice'][$i];
            $productSubTotal = $invoiceData['subTotal'][$i];

            $factor = isset(self::$factorMapping[$productName]) ? self::$factorMapping[$productName] : "Tasa";
            $productKey = isset(self::$product_keyMapping[$productName]) ? self::$product_keyMapping[$productName] : "85122101";   
            $unitKey = isset(self::$unitKeyMapping[$productName]) ? self::$unitKeyMapping[$productName] : "H87";
            $unitName = isset(self::$unitNameMapping[$productName]) ? self::$unitNameMapping[$productName] : "Pieza";    

            $item = [
                "quantity" => $productQuantity,
                "product" => [
                    "description" => $productName,
                    "product_key" => $productKey,
                    "price" => $productUnitPrice,  
                    "taxes" => [
                        [
                            "type" => "IVA",
                            "rate" => 0.16,
                            "factor" => $factor
                        ]
                    ],
                    "unit_key" => $unitKey, 
                    "unit_name" => $unitName,
                ],
            ];

            $items[] = $item;
        }

        if(count($items) == 0){
            return [
                'success' => false,
                'message' => 'No hay productos para agregar'
            ];
        }

        try {
            $invoice = $this->facturapi->Invoices->create([
                "customer" => $invoiceData['facturapiId'],
                "items" => $items,
                "payment_form" => $paymet_form,
                "use" => $use,
                "external_id" => $invoiceId,
            ]);

            return [
                'success' => true,
                'message' => 'Factura creada correctamente',
                'invoice' => $invoice
            ];
            
        } catch (Facturapi_Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear la factura: ' . $e->getMessage()
            ];
        }
    }

    public function createReceipt($data, $paymentId) {
         $payment_form = self::$paymentMapping[$data['paymentForm'] ?? ''] ?? '01';

        // 2) Normalizar products a un array de items con claves product, quantity, unitPrice
        $normalized = [];

        if (isset($data['products']) && is_array($data['products'])) {
            $p = $data['products'];

            // Caso A: arrays paralelos (product/quantity/unitPrice son arrays)
            if (isset($p['product'], $p['quantity'], $p['unitPrice'])
                && is_array($p['product']) && is_array($p['quantity']) && is_array($p['unitPrice'])) {

                $count = min(count($p['product']), count($p['quantity']), count($p['unitPrice']));
                for ($i = 0; $i < $count; $i++) {
                    $normalized[] = [
                        'product'   => $p['product'][$i]   ?? '',
                        'quantity'  => (float)($p['quantity'][$i] ?? 0),
                        'unitPrice' => (float)($p['unitPrice'][$i] ?? 0),
                    ];
                }

            // Caso B: array de items (cada elemento es un producto)
            } elseif (!empty($p) && isset($p[0]) && is_array($p[0])) {
                foreach ($p as $row) {
                    $normalized[] = [
                        'product'   => $row['product']   ?? '',
                        'quantity'  => (float)($row['quantity']  ?? 0),
                        'unitPrice' => (float)($row['unitPrice'] ?? 0),
                    ];
                }
            }
        }

        // (Opcional) Caso C: un solo producto plano en $data (por compatibilidad)
        if (empty($normalized) && isset($data['product'], $data['quantity'], $data['unitPrice'])) {
            $normalized[] = [
                'product'   => $data['product'],
                'quantity'  => (float)$data['quantity'],
                'unitPrice' => (float)$data['unitPrice'],
            ];
        }

        if (empty($normalized)) {
            throw new InvalidArgumentException('products inválido: se esperaba arrays paralelos o array de items.');
        }

        // 3) Construir items
        $items = [];
        foreach ($normalized as $item) {
            $productName      = (string)$item['product'];
            $productQuantity  = max(0, (float)$item['quantity']);
            $productUnitPrice = max(0, (float)$item['unitPrice']);

            // Defaults seguros para los catálogos
            $factor     = self::$factorMapping[$productName]      ?? 'Tasa';
            $productKey = self::$productKeyMapping[$productName]  ?? '84111506';
            $unitKey    = self::$unitKeyMapping[$productName]     ?? 'H87';
            $unitName   = self::$unitNameMapping[$productName]    ?? 'Pieza';

            $items[] = [
                'quantity' => $productQuantity,
                'product'  => [
                    'description' => $productName,
                    'product_key' => $productKey,
                    'price'       => $productUnitPrice,
                    'taxes'       => [[
                        'type'   => 'IVA',
                        'rate'   => 0.16,
                        'factor' => $factor,
                    ]],
                    'unit_key'  => $unitKey,
                    'unit_name' => $unitName,
                ],
            ];
        }

        if(count($items) == 0){
            return [
                'success' => false,
                'message' => 'No hay productos para agregar'
            ];
        }

        try {
            $receipt = $this->facturapi->Receipts->create([
                "folio_number" => $paymentId,
                "payment_form" => $payment_form,
                "items" => $items,
                "branch" => 'ESMEFIS CENTRO UNIVERSITARIO',
            ]);

            //obtener la respuesta del recibo
            $toInvoice = $this->facturapi->Receipts->retrieve($receipt->id);
            //se obtiene el id del recibo para enviarlo por correo
            $receiptId = $toInvoice->id;

            $this->facturapi->Receipts->send_by_email($receiptId, [
                $data['email']
            ]);

            return [
                'success' => true,
                'message' => 'Recibo creado correctamente',
                'receipt' => $receipt
            ];
            
        } catch (Facturapi_Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el recibo: ' . $e->getMessage()
            ];
        }
    }

    public function cancelReceipt($receiptId) {
        try {
            $receipts = $this->facturapi->Receipts->all(['folio_number' => $receiptId]);
            
            $receipt = $receipts->data[0];

            if($receipt->id){
                $this->facturapi->Receipts->cancel($receipt->id);
                return [
                    'success' => true,
                    'message' => 'Recibo cancelado correctamente'
                ];
            }else{
                return [
                    'success' => false,
                    'message' => "No se encontró el recibo."
                ];
            }

            
        } catch (Facturapi_Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cancelar el recibo: ' . $e->getMessage()
            ];
        }
    }

    public function cancelInvoice(string $invoiceId, ?string $motive = null, ?string $substituteId = null): array{
        try {
            // Armamos sólo los parámetros que vayan a usarse
            $params = [];
            
            if ($motive !== null && trim($motive) !== '') {
                $params['motive'] = trim($motive);
            }
            
            if ($substituteId !== null && trim($substituteId) !== '') {
                $params['substitution'] = trim($substituteId);
            }
            
            // Llamada: si $params sólo tiene 'motive', no enviará 'substitution'
            $invoice = $this->facturapi->Invoices->cancel($invoiceId, $params);

            // Comprobamos que realmente haya una factura cancelada
            if (empty($invoice->id)) {
                return [
                    'success' => false,
                    'message' => 'No se encontró la factura.'
                ];
            }

            return [
                'success' => true,
                'message' => 'Factura cancelada correctamente',
                'invoice' => $invoice
            ];

        } catch (Facturapi_Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al cancelar la factura: ' . $e->getMessage()
            ];
        }
    }

    public function searchReceiptByFolio($folio){
        try{
            $receipts = $this->facturapi->Receipts->all(['folio_number' => $folio]);
            
            $receipt = $receipts->data[0];

            if($receipt->id){
                return [
                    'success' => true,
                    'id' => $receipt->id,
                    'created_at' => $receipt->created_at,
                    'status' => $receipt->status,
                    'total' => $receipt->total,              
                ];
            }else{
                return [
                    'success' => false,
                    'message' => "No se encontró el recibo."
                ];
            }
        }catch (Facturapi_Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al crear el recibo: ' . $e->getMessage()
            ];
        }
    }
    
}