<?php
$error = $error ?? '';
$config = require __DIR__ . '/../../../config.php';
$baseUrl = $config['app']['base_url'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Admin Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        @font-face {
            font-family: 'Chicanos';
            src: url('<?= $baseUrl ?>assets/fonts/ChicanosPersonalUseRegular-qZDw5.woff2') format('truetype');
            font-weight: normal;
            font-style: normal;
        }

        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            font-family: Montserrat, Arial, sans-serif;
            color:#eee;
            min-height:100vh;
            display:flex;
            justify-content:center;
            align-items:center;
            padding:24px;
            background:
                radial-gradient(circle at top, rgba(255,77,109,.10), transparent 28%),
                radial-gradient(circle at bottom right, rgba(34,197,94,.08), transparent 24%),
                linear-gradient(180deg, #05070b 0%, #090d14 100%);
            position:relative;
            overflow:hidden;
        }

        .bg-glow{
            position:absolute;
            border-radius:999px;
            filter:blur(70px);
            pointer-events:none;
            opacity:.65;
        }

        .bg-glow-1{
            width:280px;
            height:280px;
            background:rgba(255,77,109,.14);
            top:6%;
            left:14%;
        }

        .bg-glow-2{
            width:260px;
            height:260px;
            background:rgba(34,197,94,.10);
            bottom:10%;
            right:14%;
        }

        .login-box{
            position:relative;
            z-index:1;
            width:100%;
            max-width:420px;
            background:linear-gradient(180deg, rgba(20,24,32,.96) 0%, rgba(15,19,27,.98) 100%);
            padding:22px 28px 24px;
            border-radius:22px;
            border:1px solid rgba(255,255,255,.06);
            box-shadow:
                0 18px 60px rgba(0,0,0,.45),
                inset 0 1px 0 rgba(255,255,255,.03);
            transform:translateY(-26px);
    }

	.login-brand{
    		display:flex;
    		align-items:center;
    		gap:10px;
    		margin-bottom:14px;
    		text-align:left;
	}
	
        .login-brand-center{
            flex-direction:column;
            justify-content:center;
            text-align:center;
            gap:14px;
        }

        .login-logo{
            width:110px;
            height:110px;
            object-fit:cover;
            border-radius:50%;
            border:2px solid rgba(255,255,255,.08);
            box-shadow:
                0 10px 26px rgba(0,0,0,.35),
                0 0 0 4px rgba(255,255,255,.02);
            background:#111;
        }

        .brand-copy h1{
            margin:0;
            font-family:'Chicanos', serif;
            font-size:54px;
            line-height:1.05;
            font-weight:normal;
            color:#f0f0f0;
            text-shadow:
                1px 1px 0 #000,
                -1px -1px 0 #000,
                1px -1px 0 #000,
                -1px 1px 0 #000,
                0 4px 12px rgba(0,0,0,.45);
        }

        .brand-copy p{
    		margin:6px 0 0;
    		font-size:11px;
    		color:rgba(255,255,255,.62);
    		text-transform:uppercase;
    		letter-spacing:1.2px;
	}

        .login-head{
            text-align:left;
            margin-bottom:14px;
        }

        .login-head h2{
            margin:0 0 6px;
            font-size:22px;
            color:#fff;
        }

        .login-head span{
            font-size:13px;
            color:rgba(255,255,255,.68);
        }

        .msg-err{
            background:rgba(220,38,38,.12);
            border:1px solid rgba(220,38,38,.28);
            color:#ffb4b4;
            padding:12px 14px;
            border-radius:12px;
            font-size:13px;
            margin-bottom:14px;
            text-align:left;
        }

        .form-group{
            margin-bottom:16px;
            text-align:left;
        }

        label{
            display:block;
            font-size:13px;
            font-weight:700;
            margin-bottom:7px;
            color:#f3f3f3;
        }

        input[type="text"],
        input[type="password"]{
            width:100%;
            height:50px;
            padding:0 14px;
            border-radius:14px;
            border:1px solid #313745;
            background:#090d14;
            color:#eee;
            font-size:14px;
            outline:none;
            transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
        }

        input[type="text"]::placeholder,
        input[type="password"]::placeholder{
            color:rgba(255,255,255,.32);
        }

        input[type="text"]:focus,
        input[type="password"]:focus{
            border-color:rgba(255,77,109,.55);
            box-shadow:0 0 0 3px rgba(255,77,109,.12);
            background:#0b1018;
        }

        .password-wrap{
            position:relative;
        }

        .password-wrap input{
            padding-right:88px;
        }

        .toggle-pass{
            position:absolute;
            right:10px;
            top:50%;
            transform:translateY(-50%);
            border:none;
            background:transparent;
            color:#cfcfcf;
            font-size:12px;
            font-weight:700;
            cursor:pointer;
            padding:4px 6px;
        }

        .toggle-pass:hover{
            color:#fff;
        }

        button[type="submit"]{
            width:100%;
            height:50px;
            margin-top:8px;
            border:none;
            border-radius:14px;
            background:linear-gradient(180deg, #2ed86e 0%, #22c55e 100%);
            color:#06110a;
            font-weight:900;
            font-size:15px;
            cursor:pointer;
            box-shadow:0 14px 28px rgba(34,197,94,.22);
            transition:transform .15s ease, filter .18s ease;
        }

        button[type="submit"]:hover{
            filter:brightness(1.03);
            transform:translateY(-1px);
        }

        button[type="submit"]:active{
            transform:translateY(0);
        }

       @media (max-width: 520px){
    		body{
        		padding:16px;
        		align-items:flex-start;
    		}			

    	.login-box{
        	padding:20px 18px 18px;
        	border-radius:18px;
       	 	transform:none;
        	margin-top:18px;
    	}

    	.login-head h2{
        	font-size:20px;
    	}
}
    </style>
</head>
<body>

<div class="bg-glow bg-glow-1"></div>
<div class="bg-glow bg-glow-2"></div>

<div class="login-box">
    <div class="login-brand login-brand-center">
        <img src="<?= $baseUrl ?>assets/imgs/logo2.webp" alt="Cholos &amp; Chulas" class="login-logo">
        <div class="brand-copy">
            <h1>
                <span>Cholos y</span><br>
                <span>Chulas</span>
            </h1>
            <p>Panel administrativo</p>
        </div>
    </div>

    <div class="login-head">
        <h2>Iniciar sesión</h2>
    </div>

    <?php if ($error): ?>
        <div class="msg-err"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="post" action="?c=auth&a=login">
        <div class="form-group">
            <label for="username">Usuario</label>
            <input type="text" id="username" name="username" required autofocus placeholder="Ingresa tu usuario" autocomplete="username">
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <div class="password-wrap">
                <input type="password" id="password" name="password" required placeholder="Ingresa tu contraseña" autocomplete="current-password">
                <button type="button" class="toggle-pass" id="togglePass">Mostrar</button>
            </div>
        </div>

        <button type="submit">Entrar</button>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btn = document.getElementById('togglePass');
    const input = document.getElementById('password');

    if (!btn || !input) return;

    btn.addEventListener('click', function () {
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';
        btn.textContent = isPassword ? 'Ocultar' : 'Mostrar';
    });
});
</script>

</body>
</html>