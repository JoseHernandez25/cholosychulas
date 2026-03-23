<?php
// app/controllers/CategoryController.php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Category.php';

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->requireRole('admin');
    }

    public function index()
    {
        $categoryModel = new Category();

        $editCategory = null;
        $id = (int)($_GET['id'] ?? 0);

        if ($id > 0) {
            $editCategory = $categoryModel->find($id);
        }

        $categories = $categoryModel->all();
        $success = !empty($_GET['ok']);
        $error   = $_GET['err'] ?? '';

        $this->render(
            'admin/categories',
            compact('categories', 'editCategory', 'success', 'error'),
            'admin'
        );
    }

    public function save()
    {
        $categoryModel = new Category();

        $data = [
            'id'     => $_POST['id'] ?? null,
            'name'   => trim($_POST['name'] ?? ''),
            'active' => isset($_POST['active']) ? 1 : 0,
        ];

        try {
            $categoryModel->save($data);
            header("Location: ?c=category&a=index&ok=1");
        } catch (Exception $e) {
            $idPart = !empty($data['id']) ? '&id=' . (int)$data['id'] : '';
            header("Location: ?c=category&a=index&err=" . urlencode($e->getMessage()) . $idPart);
        }
        exit;
    }

    public function toggle()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id <= 0) {
            header("Location: ?c=category&a=index");
            exit;
        }

        $categoryModel = new Category();
        $categoryModel->toggleActive($id);

        header("Location: ?c=category&a=index");
        exit;
    }
}