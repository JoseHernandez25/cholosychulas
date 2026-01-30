<?php
// app/views/layouts/main.php
$config   = $config ?? require __DIR__ . '/../../../config.php';
$base_url = $config['app']['base_url'];
$app_name = $config['app']['name'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($title ?? $app_name) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= $base_url ?>assets/css/pos.css?v=<?= time() ?>">
</head>
<body>

<?php require __DIR__ . '/../partials/header.php'; ?>

<main class="container">
    <?php $viewFile = $viewFile ?? __DIR__ . '/../catalog/index.php'; ?>
    <?php require $viewFile; ?>
</main>

<?php require __DIR__ . '/../partials/footer.php'; ?>

<script>
    const APP_BASE_URL    = '<?= $base_url ?>';
    const WHATSAPP_NUMBER = '<?= $config['app']['whatsapp'] ?>';
</script>
<script src="<?= $base_url ?>assets/js/app.js"></script>
</body>
</html>