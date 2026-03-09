<?php
$cashRegister = $cashRegister ?? null;
$summary = $summary ?? null;
$error = $_GET['err'] ?? '';
$ok    = $_GET['ok'] ?? '';
?>

<div class="pos-wrap">

  <?php if ($error): ?>
    <div id="flashMsg" class="msg-err" style="margin-bottom:14px;">
      <?= htmlspecialchars($error) ?>
    </div>
  <?php endif; ?>

  <?php if ($ok): ?>
    <div id="flashMsg" class="msg-ok" style="margin-bottom:14px;">
      <?= htmlspecialchars($ok) ?>
    </div>
  <?php endif; ?>

  <div class="pos-topbar">
    <div>
      <div class="pos-title">CAJA</div>
      <div class="pos-sub">Precio final · Scanner USB (HID)</div>
    </div>

    <div class="pos-actions">
      <?php if ($cashRegister): ?>
        <span class="pos-pill" id="pillConn">
          Caja abierta #<?= (int)$cashRegister['id'] ?>
        </span>
        <a class="pos-btn" href="?c=caja&a=cerrar">Cerrar / Corte</a>
      <?php else: ?>
        <span class="pos-pill danger" id="pillConn">Caja cerrada</span>
        <a class="pos-btn primary" href="?c=caja&a=abrir">Abrir caja</a>
      <?php endif; ?>

<button class="pos-btn" id="btnFocus" type="button" <?= !$cashRegister ? 'disabled' : '' ?>>Enfocar</button>
      <button class="pos-btn danger" id="btnClear" type="button">Vaciar</button>
    </div>
  </div>

<?php if ($cashRegister): ?>
  <div class="cash-info-row">
    <div class="cash-chip">
      <span class="lbl">Caja</span>
      <strong>#<?= (int)$cashRegister['id'] ?></strong>
    </div>

    <div class="cash-chip">
      <span class="lbl">Monto inicial</span>
      <strong>$<?= number_format((float)$cashRegister['opening_amount'], 2) ?></strong>
    </div>

    <div class="cash-chip">
      <span class="lbl">Estado</span>
      <strong>Abierta</strong>
    </div>

    <div class="cash-chip">
      <span class="lbl">Apertura</span>
      <strong><?= htmlspecialchars($cashRegister['opened_at']) ?></strong>
    </div>

    <div class="cash-chip">
      <span class="lbl">Ventas</span>
      <strong><?= (int)($summary['transactions'] ?? 0) ?></strong>
    </div>

    <div class="cash-chip">
      <span class="lbl">Piezas</span>
      <strong><?= (int)($summary['total_items'] ?? 0) ?></strong>
    </div>

      <div class="cash-chip cash-chip-accent">
        <span class="lbl">Total vendido</span>
        <strong>$<?= number_format((float)($summary['total_sales'] ?? 0), 2) ?></strong>
      </div>
    </div>
<?php endif; ?>

  <div class="pos-grid">
    <div class="pos-card">
      <div class="pos-card-h">
        <div class="pos-card-t">Escaneo / Carrito</div>
        <div class="pos-k">F2 cobrar · Del borrar · Esc enfocar</div>
      </div>
      <div class="pos-card-b">
        <div class="pos-scan">
          <input id="scanInput" type="text" autocomplete="off" placeholder="Escanea y Enter" <?= !$cashRegister ? 'disabled' : '' ?>>
          <div class="pos-hint">Escanea códigos 1D/2D (QR)</div>
          <div class="pos-status" id="scanStatus">
            <?= $cashRegister ? 'Esperando escaneo…' : 'Caja cerrada. No se puede escanear.' ?>
          </div>
        </div>

        <div class="pos-divider"></div>

        <div class="pos-tablewrap">
          <table class="pos-table">
            <thead>
              <tr>
                <th>Producto</th>
                <th class="r">Precio</th>
                <th class="r">Qty</th>
                <th class="r">Subtotal</th>
                <th class="r">Acción</th>
              </tr>
            </thead>
            <tbody id="tbodyCarrito">
              <tr>
                <td colspan="5" class="muted">
                  <?= $cashRegister ? 'Escanea para comenzar…' : 'Abre una caja para comenzar…' ?>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="pos-card">
      <div class="pos-card-h">
        <div class="pos-card-t">Cobro</div>
        <div class="muted" id="saleInfo">Venta nueva</div>
      </div>
      <div class="pos-card-b">
        <div class="pos-totalbox">
          <div class="pos-total">
            <div class="lbl">Total</div>
            <div class="val" id="totalTxt">$0.00</div>
          </div>

          <div class="pos-meta">
            <div class="box"><div class="t">Artículos</div><div class="v" id="itemsTxt">0</div></div>
            <div class="box"><div class="t">Piezas</div><div class="v" id="pzasTxt">0</div></div>
          </div>

          <div class="pos-pay">
            <button class="pos-btn primary" id="btnCobrar" type="button" <?= !$cashRegister ? 'disabled' : '' ?>>Cobrar (F2)</button>
            <button class="pos-btn" id="btnImprimir" type="button" <?= !$cashRegister ? 'disabled' : '' ?>>Imprimir</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  window.POS_CFG = {
    URL_BUSCAR: "<?= $base_url ?>?c=caja&a=buscarProducto",
    URL_COBRAR: "<?= $base_url ?>?c=caja&a=cobrar",
    CAJA_ABIERTA: <?= $cashRegister ? 'true' : 'false' ?>
  };

  (function () {
    const flash = document.getElementById('flashMsg');
    if (!flash) return;

    setTimeout(() => {
      flash.style.transition = 'opacity .35s ease, transform .35s ease';
      flash.style.opacity = '0';
      flash.style.transform = 'translateY(-6px)';

      setTimeout(() => {
        flash.remove();
      }, 350);
    }, 3000);

    const url = new URL(window.location.href);
    if (url.searchParams.has('ok') || url.searchParams.has('err')) {
      url.searchParams.delete('ok');
      url.searchParams.delete('err');
      window.history.replaceState({}, document.title, url.toString());
    }
  })();
</script>

<script src="<?= $base_url ?>assets/js/pos.js?v=<?= time() ?>"></script>