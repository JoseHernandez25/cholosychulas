<?php
// app/controllers/LabelController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';

class LabelController extends Controller
{
    public function __construct()
    {
        $this->requireRole('admin');
    }

    public function index()
    {
        $productModel = new Product();
        $labels = $productModel->allLabelsForAdmin();

        $this->render('admin/labels', [
            'title'  => 'Etiquetas',
            'labels' => $labels
        ], 'admin');
    }
}