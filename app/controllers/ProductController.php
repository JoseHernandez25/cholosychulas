<?php
// app/controllers/ProductController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Category.php';

class ProductController extends Controller
{
    public function __construct()
    {
        $this->requireRole('admin');
    }

    // =========================================
    // EXISTENCIAS (LISTADO)
    // URL: ?c=product&a=index
    // =========================================
    public function index()
    {
        $productModel = new Product();
        $products = $productModel->allForAdmin();

        $success = !empty($_GET['ok']);
        $error   = $_GET['err'] ?? '';

        $this->render('admin/products', compact('products', 'success', 'error'), 'admin');
    }

    // =========================================
    // REGISTRAR PRODUCTO (FORM VACÍO)
    // URL: ?c=product&a=create
    // =========================================
    public function create()
    {
        $categoryModel = new Category();
        $categories = $categoryModel->allActive();

        $editProduct = null;
        $success = !empty($_GET['ok']);
        $error   = $_GET['err'] ?? '';

        $this->render('admin/product_form', compact('categories', 'editProduct', 'success', 'error'), 'admin');
    }

    // =========================================
    // EDITAR PRODUCTO (FORM PRECARGADO)
    // URL: ?c=product&a=edit&id=#
    // =========================================
    public function edit()
    {
        if (empty($_GET['id'])) {
            header("Location: ?c=product&a=index");
            exit;
        }

        $productModel  = new Product();
        $categoryModel = new Category();

        $editProduct = $productModel->find((int)$_GET['id']);
        $categories  = $categoryModel->allActive();

        $success = !empty($_GET['ok']);
        $error   = $_GET['err'] ?? '';

        $this->render('admin/product_form', compact('categories', 'editProduct', 'success', 'error'), 'admin');
    }

    // =========================================
    // GUARDAR (nuevo / editar)
    // URL: ?c=product&a=save
    // =========================================
    public function save()
    {
        $productModel = new Product();

        $id            = $_POST['id']            ?? null;
        $name          = trim($_POST['name']     ?? '');
        $price         = $_POST['price']         ?? 0;
        $category_id   = $_POST['category_id']   ?? null;
        $stock         = $_POST['stock']         ?? 0;
        $description   = trim($_POST['description'] ?? '');
        $sort_order    = $_POST['sort_order']    ?? null;
        $active        = isset($_POST['active']) ? 1 : 0;
        $current_image = $_POST['current_image'] ?? '';

        if ($name === '' || !$category_id) {
            $err = urlencode('Nombre y categoría son obligatorios');
            header("Location: ?c=product&a=create&err={$err}");
            exit;
        }

        // ==============================
        // IMAGEN (assets/imgs)
        // ==============================
        $image_url = $current_image;

        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $uploadsDir = 'assets/imgs';

            if (!is_dir($uploadsDir)) {
                mkdir($uploadsDir, 0777, true);
            }

            $ext      = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = time() . '_' . mt_rand(1000, 9999) . '.' . $ext;
            $destPath = $uploadsDir . '/' . $filename;

            if (move_uploaded_file($_FILES['image']['tmp_name'], $destPath)) {
                $image_url = $destPath;
            }
        }

        $data = [
            'id'          => $id ?: null,
            'name'        => $name,
            'price'       => $price,
            'category_id' => $category_id,
            'stock'       => $stock,
            'description' => $description,
            'sort_order'  => $sort_order,
            'active'      => $active,
            'image_url'   => $image_url,
        ];

        try {
            // 🔥 el barcode CC se genera SOLO en el modelo
            $productModel->save($data);
            header("Location: ?c=product&a=index&ok=1");
        } catch (Exception $e) {
            $err = urlencode('Error al guardar: ' . $e->getMessage());
            header("Location: ?c=product&a=index&err={$err}");
        }
        exit;
    }

    // =========================================
    // ACTIVAR / DESACTIVAR
    // URL: ?c=product&a=toggle&id=#
    // =========================================
    public function toggle()
    {
        if (empty($_GET['id'])) {
            header("Location: ?c=product&a=index");
            exit;
        }

        $productModel = new Product();
        $productModel->toggleActive((int)$_GET['id']);

        header("Location: ?c=product&a=index");
        exit;
    }
}