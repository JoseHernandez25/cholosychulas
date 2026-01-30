<?php
// app/controllers/CatalogController.php
require_once __DIR__ . '/../models/Product.php';

class CatalogController extends Controller
{
    public function index()
    {
        $productModel = new Product();
        $products     = $productModel->allActiveForCatalog();

        // Agregar variantes a cada producto
        foreach ($products as &$p) {
            $p['variants'] = $productModel->getVariants((int)$p['id']);
        }
        unset($p);

        $config = require __DIR__ . '/../../config.php';

        $this->render('catalog/index', [
            'title'    => 'Catálogo',
            'products' => $products,
            'config'   => $config,
        ]);
    }
}