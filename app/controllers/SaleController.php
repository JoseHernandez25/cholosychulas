<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Sale.php';

class SaleController extends Controller
{
    public function __construct()
    {
        $this->requireLogin();
    }

    public function index()
    {
        $saleModel = new Sale();
        $sales = $saleModel->allForAdmin();

        $this->render('admin/sales', compact('sales'), 'admin');
    }

    public function show()
    {
        $id = (int)($_GET['id'] ?? 0);

        if ($id <= 0) {
            header("Location: ?c=sale&a=index");
            exit;
        }

        $saleModel = new Sale();

        $sale = $saleModel->findForAdmin($id);

        if (!$sale) {
            header("Location: ?c=sale&a=index");
            exit;
        }

        $items = $saleModel->itemsBySale($id);

        $this->render('admin/sale_show', compact('sale', 'items'), 'admin');
    }
}