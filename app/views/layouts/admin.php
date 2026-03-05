<?php
// app/views/layouts/admin.php
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($title ?? 'Admin') ?></title>

  <link rel="stylesheet" href="<?= $base_url ?>assets/css/pos.css?v=<?= time() ?>">
  <link rel="stylesheet" href="<?= $base_url ?>assets/css/admin.css?v=<?= time() ?>">
</head>

<body class="is-admin">

  <?php include __DIR__ . '/../partials/admin_menu.php'; ?>

  <div class="admin-page">
    <?php include __DIR__ . '/../partials/admin_topbar.php'; ?>

    <main class="admin-content">
      <?php require $viewFile; ?>
    </main>
  </div>

</body>
</html>