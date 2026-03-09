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
        'title' => 'Caja',
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
            'title' => 'Abrir caja'
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
            'title' => 'Cerrar caja',
            'cashRegister' => $cashRegister,
            'summary' => $summary
        ], 'admin');
    }

    // AJAX: buscar producto por barcode
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

            echo json_encode([
                'ok' => true,
                'producto' => [
                    'id'     => (int)$product['id'],
                    'nombre' => $product['name'],
                    'precio' => (float)$product['price']
                ]
            ]);
        } catch (Throwable $e) {
            echo json_encode(['ok' => false, 'msg' => 'Error servidor']);
        }
    }

    // AJAX: registrar venta
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

            // aquí mandamos caja y usuario a la venta
            $result = Sale::createFromItems($items, [
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