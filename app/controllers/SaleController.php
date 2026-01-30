<?php
// app/controllers/SaleController.php
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Product.php';

class SaleController
{
    private function requireLogin()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['admin_logged'])) {
            header("Location: index.php?page=auth&action=login");
            exit;
        }
    }

    public function index()
    {
        $this->requireLogin();

        $saleModel    = new Sale();
        $productModel = new Product();

        // productos para el combo
        $products = $productModel->allForAdmin();   // ya lo tienes
        // ventas para el listado
        $sales    = $saleModel->allWithProduct();

        $success = !empty($_GET['ok']);
        $error   = $_GET['err'] ?? '';

        include __DIR__ . '/../views/admin/sales.php';
    }

    public function save()
    {
        $this->requireLogin();

        $saleModel = new Sale();

        $product_id = $_POST['product_id'] ?? null;
        $quantity   = $_POST['quantity']   ?? null;
        $note       = trim($_POST['note']  ?? '');

        try {
            $saleModel->create([
                'product_id' => $product_id,
                'quantity'   => $quantity,
                'note'       => $note,
            ]);

            header("Location: index.php?page=admin_sales&ok=1");
        } catch (Exception $e) {
            $err = urlencode('Error al registrar la venta: ' . $e->getMessage());
            header("Location: index.php?page=admin_sales&err={$err}");
        }
        exit;
    }
}