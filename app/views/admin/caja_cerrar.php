<?php
$cashRegister = $cashRegister ?? [];
$summary      = $summary ?? [];
$error        = $_GET['err'] ?? '';
?>

<div class="panel panel-right form-panel product-form-panel">
  <div class="panel-head">
    <div>
      <h2 class="title-panel">Cerrar / Corte de caja</h2>
      <div class="panel-sub">Revisa el resumen antes de cerrar la caja.</div>
    </div>

    <a class="btn-ghost" href="?c=caja&a=index">← Volver</a>
  </div>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="sale-summary-grid">
    <div class="sale-box">
      <span class="sale-label">Caja ID</span>
      <strong>#<?= (int)($cashRegister['id'] ?? 0) ?></strong>
    </div>

    <div class="sale-box">
      <span class="sale-label">Monto inicial</span>
      <strong>$<?= number_format((float)($cashRegister['opening_amount'] ?? 0), 2) ?></strong>
    </div>

    <div class="sale-box">
      <span class="sale-label">Ventas</span>
      <strong><?= (int)($summary['transactions'] ?? 0) ?></strong>
    </div>

    <div class="sale-box">
      <span class="sale-label">Piezas</span>
      <strong><?= (int)($summary['total_items'] ?? 0) ?></strong>
    </div>

    <div class="sale-box">
      <span class="sale-label">Total vendido</span>
      <strong>$<?= number_format((float)($summary['total_sales'] ?? 0), 2) ?></strong>
    </div>
  </div>

  <form class="admin-form" method="post" action="?c=caja&a=cerrar" style="margin-top:20px;">
    <div class="form-grid">
      <div class="form-group form-span-2">
        <label>Monto final en caja</label>
        <input type="number" step="0.01" min="0" name="closing_amount" required value="0.00">
      </div>
    </div>

    <div class="form-actions">
      <a class="btn-ghost" href="?c=caja&a=index">Cancelar</a>
      <button type="submit" class="primary">Cerrar caja</button>
    </div>
  </form>
</div>