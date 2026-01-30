<?php
// app/controllers/CajaController.php

require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';

class CajaController extends Controller
{
    public function index()
    {
        $this->render('admin/caja', [
            'title' => 'Caja'
        ], 'admin');
    }

    // AJAX: buscar producto por barcode
    public function buscarProducto()
    {
        header('Content-Type: application/json; charset=utf-8');

        $barcode = trim($_GET['barcode'] ?? '');
        if ($barcode === '') {
            echo json_encode(['ok'=>false, 'msg'=>'Barcode vacío']);
            return;
        }

        try {
            $product = Product::findByBarcode($barcode);

            if (!$product) {
                echo json_encode(['ok'=>false, 'msg'=>'Producto no encontrado']);
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
            echo json_encode(['ok'=>false, 'msg'=>'Error servidor']);
        }
    }

    // AJAX: registrar venta
    public function cobrar()
    {
        header('Content-Type: application/json; charset=utf-8');

        $data = json_decode(file_get_contents('php://input'), true);
        $items = $data['items'] ?? [];

        if (!is_array($items) || count($items) === 0) {
            echo json_encode(['ok'=>false, 'msg'=>'Carrito vacío']);
            return;
        }

        try {
            // ✅ OJO: aquí NO usamos Sale::create() porque no existe en tu proyecto.
            // Vamos a usar un método claro: Sale::createFromItems()
            $result = Sale::createFromItems($items);

            echo json_encode([
                'ok' => true,
                'id_venta' => (int)$result['id_venta'],
                'ticket_html' => $result['ticket_html'] ?? ''
            ]);
        }catch (Throwable $e) {
                http_response_code(400);
                echo json_encode([
                'ok' => false,
                'msg' => $e->getMessage() // mensaje humano (stock insuficiente, etc.)
                ]);
                exit;
                }
    
            }

}