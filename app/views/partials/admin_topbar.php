<?php
$userName = $_SESSION['user']['name'] ?? ($_SESSION['user']['username'] ?? 'Usuario');
$role     = $_SESSION['user']['role'] ?? '';
?>
<header class="admin-topbar">
  <div class="admin-topbar-left">
    <button class="admin-toggle" id="adminSidebarToggle" type="button" aria-label="Ocultar o mostrar menú" aria-expanded="true" title="Ocultar / mostrar menú">
      ☰
    </button>

    <div class="admin-brand-top">Cholos y Chulas</div>
    <div class="admin-topbar-sub">Panel Admin</div>
  </div>

  <div class="admin-topbar-right">
    <div class="admin-user">
      <span class="name"><?= htmlspecialchars($userName) ?></span>
      <?php if ($role): ?>
        <span class="role"><?= htmlspecialchars($role) ?></span>
      <?php endif; ?>
    </div>

    <a class="admin-ghost" href="?c=catalog&a=index">Catálogo</a>
    <a class="admin-logout" href="?c=auth&a=logout">Salir</a>
  </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const btn = document.getElementById('adminSidebarToggle');
  const body = document.body;
  const key = 'cc_admin_sidebar_collapsed';

  const params = new URLSearchParams(window.location.search);
  const controller = (params.get('c') || '').toLowerCase();
  const action = (params.get('a') || '').toLowerCase();

  // vistas donde quieres colapsar automáticamente
  const autoCollapseViews = [
    'caja:index',
    'caja:abrir',
    'caja:cerrar'
  ];

  const currentView = controller + ':' + action;
  const shouldAutoCollapse = autoCollapseViews.includes(currentView);

  if (shouldAutoCollapse) {
    body.classList.add('sidebar-collapsed');
  } else if (localStorage.getItem(key) === '1') {
    body.classList.add('sidebar-collapsed');
  }

  function syncToggleState() {
    if (!btn) return;
    btn.setAttribute(
      'aria-expanded',
      body.classList.contains('sidebar-collapsed') ? 'false' : 'true'
    );
  }

  syncToggleState();

  if (btn) {
    btn.addEventListener('click', function () {
      body.classList.toggle('sidebar-collapsed');

      localStorage.setItem(
        key,
        body.classList.contains('sidebar-collapsed') ? '1' : '0'
      );

      syncToggleState();
    });
  }
});
</script>