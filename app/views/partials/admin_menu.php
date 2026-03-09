<?php
$currentC = strtolower($_GET['c'] ?? '');
$currentA = strtolower($_GET['a'] ?? '');

$isCajaIndex    = ($currentC === 'caja'   && $currentA === 'index');
$isCajaAbrir    = ($currentC === 'caja'   && $currentA === 'abrir');
$isCajaCerrar   = ($currentC === 'caja'   && $currentA === 'cerrar');

$isSaleIndex    = ($currentC === 'sale'   && $currentA === 'index');

$isProductIndex = ($currentC === 'product' && $currentA === 'index');
$isProductForm  = ($currentC === 'product' && in_array($currentA, ['create', 'edit'], true));
?>
<aside class="admin-sidebar" id="adminSidebar">
  <div class="admin-sidebar-brand">
  <img src="assets/imgs/logo2.webp" alt="Cholos & Chulas" class="admin-sidebar-logo">
</div>
  <nav class="admin-nav">
    <div class="nav-section">Caja</div>

    <a class="nav-link<?php echo $isCajaIndex ? ' active' : ''; ?>" href="?c=caja&a=index" title="Caja">
      <span class="menu-icon">💰</span>
      <span class="menu-text">Caja</span>
    </a>

    <a class="nav-link<?php echo $isCajaAbrir ? ' active' : ''; ?>" href="?c=caja&a=abrir" title="Abrir caja">
      <span class="menu-icon">🔓</span>
      <span class="menu-text">Abrir caja</span>
    </a>

    <a class="nav-link<?php echo $isCajaCerrar ? ' active' : ''; ?>" href="?c=caja&a=cerrar" title="Cerrar / Corte">
      <span class="menu-icon">🧾</span>
      <span class="menu-text">Cerrar / Corte</span>
    </a>

    <div class="nav-section">Ventas</div>

    <a class="nav-link<?php echo $isSaleIndex ? ' active' : ''; ?>" href="?c=sale&a=index" title="Ver ventas">
      <span class="menu-icon">🛒</span>
      <span class="menu-text">Ver ventas</span>
    </a>

    <div class="nav-section">Inventario</div>

    <a class="nav-link<?php echo $isProductIndex ? ' active' : ''; ?>" href="?c=product&a=index" title="Existencias">
      <span class="menu-icon">📦</span>
      <span class="menu-text">Existencias</span>
    </a>

    <div class="nav-section">Productos</div>

    <a class="nav-link<?php echo $isProductForm ? ' active' : ''; ?>" href="?c=product&a=create" title="Registrar producto">
      <span class="menu-icon">➕</span>
      <span class="menu-text">Registrar producto</span>
    </a>
  </nav>
</aside>