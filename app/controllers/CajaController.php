<?php
// app/controllers/CajaController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/CashRegister.php';

class CajaController extends Controller
{
    public function __construct()
    {
        $this->requireLogin();
    }

    public function index()
    {
        $cashRegisterModel = new CashRegister();
        $userId = (int)$_SESSION['user']['id'];

        $cashRegister = $cashRegisterModel->getOpenByUser($userId);
        $summary = null;

        if ($cashRegister) {
            $summary = $cashRegisterModel->getSalesSummary((int)$cashRegister['id']);
        }

        $this->render('admin/caja', [
            'title' =>'Caja | Cholos & Chulas',
            'cashRegister' => $cashRegister,
            'summary' => $summary
        ], 'admin');
    }

    public function abrir()
    {
        $cashRegisterModel = new CashRegister();
        $userId = (int)$_SESSION['user']['id'];

        $cashRegister = $cashRegisterModel->getOpenByUser($userId);

        if ($cashRegister) {
            header("Location: ?c=caja&a=index&err=" . urlencode('Ya tienes una caja abierta'));
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $openingAmount = (float)($_POST['opening_amount'] ?? 0);

            $cashRegisterModel->open($userId, $openingAmount);

            header("Location: ?c=caja&a=index&ok=" . urlencode('Caja abierta correctamente'));
            exit;
        }

        $this->render('admin/caja_abrir', [
            'title' => 'Abrir caja | Cholos & Chulas'
        ], 'admin');
    }

    public function cerrar()
    {
        $cashRegisterModel = new CashRegister();
        $userId = (int)$_SESSION['user']['id'];

        $cashRegister = $cashRegisterModel->getOpenByUser($userId);

        if (!$cashRegister) {
            header("Location: ?c=caja&a=index&err=" . urlencode('No tienes una caja abierta'));
            exit;
        }

        $summary = $cashRegisterModel->getSalesSummary((int)$cashRegister['id']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $closingAmount = (float)($_POST['closing_amount'] ?? 0);

            $cashRegisterModel->close(
                (int)$cashRegister['id'],
                $closingAmount,
                (float)($summary['total_sales'] ?? 0),
                (int)($summary['total_items'] ?? 0),
                (int)($summary['transactions'] ?? 0)
            );

            header("Location: ?c=caja&a=index&ok=" . urlencode('Caja cerrada correctamente'));
            exit;
        }

        $this->render('admin/caja_cerrar', [
            'title' => 'Cerrar / Corte | Cholos & Chulas',
            'cashRegister' => $cashRegister,
            'summary' => $summary
        ], 'admin');
    }

    // =========================================
    // AJAX: buscar producto por barcode
    // =========================================
    public function buscarProducto()
    {
        header('Content-Type: application/json; charset=utf-8');

        $barcode = trim($_GET['barcode'] ?? '');
        if ($barcode === '') {
            echo json_encode(['ok' => false, 'msg' => 'Barcode vacío']);
            return;
        }
        
        try {
            $product = Product::findByBarcode($barcode);

            if (!$product) {
                echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado']);
                return;
            }

            if ((int)($product['stock'] ?? 0) <= 0) {
                echo json_encode(['ok' => false, 'msg' => 'Sin stock disponible']);
                return;
            }

            $nombre = $product['name'];
            if (!empty($product['variant_id'])) {
                $parts = [];
                if (!empty($product['size'])) {
                    $parts[] = $product['size'];
                }
                if (!empty($product['color'])) {
                    $parts[] = $product['color'];
                }
                if (!empty($parts)) {
                    $nombre .= ' - ' . implode(' / ', $parts);
                }
            }

            echo json_encode([
                'ok' => true,
                'producto' => [
                    'id'           => (int)$product['id'],
                    'variant_id'   => !empty($product['variant_id']) ? (int)$product['variant_id'] : null,
                    'nombre'       => $nombre,
                    'nombre_base'  => $product['name'],
                    'precio'       => (float)$product['price'],
                    'stock'        => (int)$product['stock'],
                    'size'         => $product['size'] ?? null,
                    'color'        => $product['color'] ?? null,
                    'barcode'      => $product['barcode'] ?? null,
                    'has_variants' => !empty($product['has_variants']) ? 1 : 0
                ]
            ]);
        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'ok' => false,
                'msg' => 'Error servidor'
            ]);
        }
        
    }

    // =========================================
    // AJAX: registrar venta
    // =========================================
    public function cobrar()
    {
        header('Content-Type: application/json; charset=utf-8');

        $data  = json_decode(file_get_contents('php://input'), true);
        $items = $data['items'] ?? [];

        if (!is_array($items) || count($items) === 0) {
            echo json_encode(['ok' => false, 'msg' => 'Carrito vacío']);
            return;
        }

        try {
            $cashRegisterModel = new CashRegister();
            $userId = (int)$_SESSION['user']['id'];

            $cashRegister = $cashRegisterModel->getOpenByUser($userId);

            if (!$cashRegister) {
                http_response_code(400);
                echo json_encode([
                    'ok' => false,
                    'msg' => 'No hay una caja abierta'
                ]);
                return;
            }

            // Normalizar items para soportar variantes
            $normalizedItems = [];

            foreach ($items as $item) {
                $normalizedItems[] = [
                    'id'          => (int)($item['id'] ?? 0),
                    'variant_id'  => isset($item['variant_id']) && $item['variant_id'] !== '' ? (int)$item['variant_id'] : null,
                    'nombre'      => (string)($item['nombre'] ?? ''),
                    'precio'      => (float)($item['precio'] ?? 0),
                    'qty'         => max(1, (int)($item['qty'] ?? 1)),
                    'size'        => $item['size'] ?? null,
                    'color'       => $item['color'] ?? null,
                    'barcode'     => $item['barcode'] ?? null
                ];
            }

            $result = Sale::createFromItems($normalizedItems, [
                'cash_register_id' => (int)$cashRegister['id'],
                'user_id' => $userId
            ]);

            echo json_encode([
                'ok' => true,
                'id_venta' => (int)$result['id_venta'],
                'ticket_html' => $result['ticket_html'] ?? ''
            ]);
        } catch (Throwable $e) {
            http_response_code(400);
            echo json_encode([
                'ok' => false,
                'msg' => $e->getMessage()
            ]);
            exit;
        }
    }
}