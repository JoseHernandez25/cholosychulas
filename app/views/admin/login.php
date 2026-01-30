<?php
// $error viene desde AuthController::login()
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body {
            margin:0;
            font-family: Arial, sans-serif;
            background:#0f1012;
            color:#eee;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }
        .login-box {
            background:#16181d;
            padding:25px 30px;
            border-radius:12px;
            box-shadow:0 0 15px rgba(0,0,0,0.6);
            width:300px;
            text-align:center;
        }
        h1 {
            margin-top:0;
            margin-bottom:20px;
            font-size:20px;
        }
        .form-group {
            margin-bottom:12px;
            text-align:left;
        }
        label {
            display:block;
            font-size:13px;
            margin-bottom:4px;
        }
        input[type="text"],
        input[type="password"] {
            width:100%;
            padding:8px;
            border-radius:6px;
            border:1px solid #333;
            background:#000;
            color:#eee;
            font-size:13px;
        }
        button {
            width:100%;
            padding:9px;
            border:none;
            border-radius:8px;
            background:#25D366;
            color:#000;
            font-weight:bold;
            cursor:pointer;
            margin-top:10px;
        }
        button:hover {
            filter:brightness(1.05);
        }
        .msg-err {
            background:#3a1111;
            color:#ff9a9a;
            padding:8px 10px;
            border-radius:6px;
            font-size:12px;
            margin-bottom:10px;
            text-align:left;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h1>Admin Login</h1>

    <?php if ($error): ?>
        <div class="msg-err"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <!-- IMPORTANTE: action apunta al router con page=auth&action=doLogin -->
    <form method="post" action="index.php?page=auth&action=doLogin">
        <div class="form-group">
            <label>Usuario</label>
            <input type="text" name="username" required>
        </div>

        <div class="form-group">
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>

        <button type="submit">Entrar</button>
    </form>
</div>

</body>
</html>