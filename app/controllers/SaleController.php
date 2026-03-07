<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Sale.php';

class SaleController extends Controller
{

public function index()
{

$saleModel = new Sale();

$sales = $saleModel->allForAdmin();

$this->render('admin/sales', compact('sales'), 'admin');

}


public function show()
{

$id = $_GET['id'] ?? null;

if(!$id)
{
header("Location: ?c=sale&a=index");
exit;
}

$saleModel = new Sale();

$sale = $saleModel->findForAdmin($id);

$items = $saleModel->itemsBySale($id);

$this->render('admin/sale_show', compact('sale','items'),'admin');

}

}