<?php
$error = $_GET['err'] ?? '';
?>

<div class="panel panel-right">
  <div class="panel-head">
    <div>
      <h2 class="title-panel">Abrir caja</h2>
      <div class="panel-sub">Captura el monto inicial con el que arrancará la caja.</div>
    </div>

    <a class="btn-ghost" href="?c=caja&a=index">← Volver</a>
  </div>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" method="post" action="?c=caja&a=abrir">
    <div class="form-grid">
      <div class="form-group form-span-2">
        <label>Monto inicial</label>
        <input type="number" step="0.01" min="0" name="opening_amount" required value="0.00">
      </div>
    </div>

    <div class="form-actions">
      <a class="btn-ghost" href="?c=caja&a=index">Cancelar</a>
      <button type="submit" class="primary">Abrir caja</button>
    </div>
  </form>
</div>