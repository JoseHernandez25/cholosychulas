<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/User.php';

class AuthController extends Controller
{
    public function login()
    {
        // Si ya está logueado, al admin (caja)
        if (!empty($_SESSION['user'])) {
            header("Location: ?c=caja&a=index");
            exit;
        }

        $title = 'Iniciar sesión | Cholos & Chulas';
        $error = null;

        // Si viene POST, procesa aquí mismo (ya no uses doLogin aparte)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = trim($_POST['username'] ?? '');
            $password = (string)($_POST['password'] ?? '');

            $u = User::findByUsername($username);

            if (!$u || (int)$u['is_active'] !== 1) {
                $error = 'Usuario o contraseña incorrectos';
            } elseif (!password_verify($password, $u['password_hash'])) {
                $error = 'Usuario o contraseña incorrectos';
            } else {
                // Guardar sesión limpia (sin hash)
                $_SESSION['user'] = [
                    'id'       => (int)$u['id'],
                    'username' => $u['username'],
                    'name'     => $u['name'],
                    'role'     => $u['role'],
                ];

                header("Location: ?c=caja&a=index");
                exit;
            }
        }

        // Renderiza tu vista existente: app/views/admin/login.php
        // OJO: esta vista debe tener form method="post"
        include __DIR__ . '/../views/admin/login.php';
    }

    public function logout()
    {
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header("Location: ?c=auth&a=login");
        exit;
    }
}