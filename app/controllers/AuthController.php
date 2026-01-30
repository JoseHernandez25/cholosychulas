<?php
// app/controllers/AuthController.php

class AuthController
{
    private function startSession()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function login()
    {
        $this->startSession();

        // si ya está logueado, manda directo al admin
        if (!empty($_SESSION['admin_logged'])) {
            header("Location: index.php?page=admin_products");
            exit;
        }

        $error = !empty($_GET['err']) ? $_GET['err'] : '';

        include __DIR__ . '/../views/admin/login.php';
    }

    public function doLogin()
    {
        $this->startSession();

        $user = $_POST['username'] ?? '';
        $pass = $_POST['password'] ?? '';

        // 🔐 Credenciales HARDCODEADAS (cámbialas a lo que quieras)
        $ADMIN_USER = 'admin';
        $ADMIN_PASS = '1234';

        if ($user === $ADMIN_USER && $pass === $ADMIN_PASS) {
            $_SESSION['admin_logged'] = true;
            header("Location: index.php?page=admin_products");
            exit;
        } else {
            $err = urlencode('Usuario o contraseña incorrectos');
            header("Location: index.php?page=auth&action=login&err={$err}");
            exit;
        }
    }

    public function logout()
    {
        $this->startSession();

        $_SESSION = [];
        session_destroy();

        header("Location: index.php?page=auth&action=login");
        exit;
    }
}