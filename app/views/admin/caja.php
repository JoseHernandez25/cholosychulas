<div class="pos-wrap">

  <div class="pos-topbar">
    <div>
      <div class="pos-title">CAJA</div>
      <div class="pos-sub">Precio final · Scanner USB (HID)</div>
    </div>

    <div class="pos-actions">
      <span class="pos-pill" id="pillConn">Listo</span>
      <button class="pos-btn" id="btnFocus" type="button">Enfocar</button>
      <button class="pos-btn danger" id="btnClear" type="button">Vaciar</button>
    </div>
  </div>

  <div class="pos-grid">
    <div class="pos-card">
      <div class="pos-card-h">
        <div class="pos-card-t">Escaneo / Carrito</div>
        <div class="pos-k">F2 cobrar · Del borrar · Esc enfocar</div>
      </div>
      <div class="pos-card-b">
        <div class="pos-scan">
          <input id="scanInput" type="text" autocomplete="off" placeholder="Escanea y Enter">
          <div class="pos-hint">Escanea códigos 1D/2D (QR)</div>
          <div class="pos-status" id="scanStatus">Esperando escaneo…</div>
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
              <tr><td colspan="5" class="muted">Escanea para comenzar…</td></tr>
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
            <button class="pos-btn primary" id="btnCobrar" type="button">Cobrar (F2)</button>
            <button class="pos-btn" id="btnImprimir" type="button">Imprimir</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  window.POS_CFG = {
    URL_BUSCAR: "<?= $base_url ?>?c=caja&a=buscarProducto",
    URL_COBRAR: "<?= $base_url ?>?c=caja&a=cobrar"
  };
</script>
<script src="<?= $base_url ?>assets/js/pos.js?v=<?= time() ?>"></script>