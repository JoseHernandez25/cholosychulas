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
            'title'  => 'Etiquetas | Cholos & Chulas',
            'labels' => $labels
        ], 'admin');
    }

    public function printSheet()
    {
        $productModel = new Product();
        $labels = $productModel->allLabelsForAdmin();

        // El usuario elige columnas y filas desde el modal
        $cols = isset($_GET['cols']) ? (int)$_GET['cols'] : 4;
        $rows = isset($_GET['rows']) ? (int)$_GET['rows'] : 3;

        // Límites para evitar errores
        if ($cols < 1) $cols = 1;
        if ($rows < 1) $rows = 1;

        if ($cols > 10) $cols = 10;
        if ($rows > 15) $rows = 15;

        $totalSlots = $cols * $rows;

        $mapParam = $_GET['map'] ?? '';
        $selectedMap = [];

        if ($mapParam !== '') {
            $decoded = json_decode(base64_decode($mapParam), true);

            if (is_array($decoded)) {
                foreach ($decoded as $slot => $labelKey) {
                    $slot = (int)$slot;

                    if ($slot >= 1 && $slot <= $totalSlots && is_string($labelKey) && $labelKey !== '') {
                        $selectedMap[$slot] = $labelKey;
                    }
                }
            }
        }
        $this->render('admin/labels_sheet', [
            'title'       => 'Hoja de etiquetas',
            'labels'      => $labels,
            'selectedMap' => $selectedMap,
            'cols'        => $cols,
            'rows'        => $rows,
            'totalSlots'  => $totalSlots
        ], '');
    }
}