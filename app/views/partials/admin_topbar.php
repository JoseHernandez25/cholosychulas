<?php
$userName = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Usuario');
$role     = $_SESSION['user']['role'] ?? '';
?>
<header class="admin-topbar">
  <div class="admin-topbar-left">
    <div class="admin-brand-top">Cholos &amp; Chulas</div>
    <div class="admin-topbar-sub">Panel Admin</div>
  </div>

  <div class="admin-topbar-right">
    <div class="admin-user">
      <span class="name"><?= htmlspecialchars($userName) ?></span>
      <?php if ($role): ?>
        <span class="role"><?= htmlspecialchars($role) ?></span>
      <?php endif; ?>
    </div>

    <!-- opcional: link al catálogo pero como icono/pequeño -->
    <a class="admin-ghost" href="?c=catalog&a=index">Catálogo</a>

    <a class="admin-logout" href="?c=auth&a=logout">Salir</a>
  </div>
</header>