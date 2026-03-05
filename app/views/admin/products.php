<?php
$success  = $success  ?? false;
$error    = $error    ?? '';
$products = $products ?? [];
?>

<div class="panel panel-left">
  <div class="panel-head">
    <h2 class="title-panel">Existencias</h2>
    <a class="btn-primary" href="?c=product&a=create">+ Nuevo producto</a>
  </div>

  <?php if ($success): ?>
    <div class="msg-ok">Producto guardado correctamente.</div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Barcode</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $row): ?>
          <tr>
            <td><?= (int)$row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
            <td>$<?= number_format((float)$row['price'], 2) ?></td>
            <td><?= (int)($row['stock'] ?? 0) ?></td>

            <td class="barcode-cell">
              <div class="barcode-code"><?= htmlspecialchars($row['barcode'] ?? '') ?></div>

              <?php if (!empty($row['barcode_path'])): ?>
                <button
                  type="button"
                  class="btn-mini"
                  data-barcode-src="<?= htmlspecialchars($row['barcode_path']) ?>"
                  data-barcode-code="<?= htmlspecialchars($row['barcode'] ?? '') ?>"
                >Ver</button>
              <?php else: ?>
                <span class="muted">—</span>
              <?php endif; ?>
            </td>

            <td>
              <span class="badge <?= !empty($row['active']) ? 'badge-active' : 'badge-inactive' ?>">
                <?= !empty($row['active']) ? 'ACTIVO' : 'INACTIVO' ?>
              </span>
            </td>

            <td>
            <a class="btn-link" href="?c=product&a=edit&id=<?= (int)$row['id'] ?>">Editar</a>
              <a class="btn-link"
                 href="?c=product&a=toggle&id=<?= (int)$row['id'] ?>"
                 onclick="return confirm('¿Cambiar estado de este producto?');">
                <?= !empty($row['active']) ? 'Desactivar' : 'Activar' ?>
              </a>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="8">No hay productos registrados.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- MODAL BARCODE -->
<div id="barcodeModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop" data-close="1"></div>

  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="barcodeTitle">
    <div class="modal-head">
      <div>
        <div id="barcodeTitle" class="modal-title">Barcode</div>
        <div id="barcodeSub" class="modal-sub"></div>
      </div>

      <button type="button" class="modal-x" data-close="1">✕</button>
    </div>

    <div class="modal-body">
      <div class="barcode-preview">
        <img id="barcodeImg" src="" alt="barcode">
      </div>
    </div>

    <div class="modal-actions">
      <button type="button" class="btn-ghost" data-close="1">Cerrar</button>
      <button type="button" class="btn-primary" id="btnPrintBarcode">Imprimir</button>
    </div>
  </div>
</div>

<script>
(function(){
  const modal = document.getElementById('barcodeModal');
  const img   = document.getElementById('barcodeImg');
  const sub   = document.getElementById('barcodeSub');

  function openModal(src, code){
    img.src = src;
    sub.textContent = code ? ('Código: ' + code) : '';
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
  }

  function closeModal(){
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    img.src = '';
    sub.textContent = '';
  }

  // Click en botones "Ver"
  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-mini[data-barcode-src]');
    if(btn){
      openModal(btn.getAttribute('data-barcode-src'), btn.getAttribute('data-barcode-code'));
      return;
    }

    // cerrar
    if(e.target && e.target.getAttribute('data-close') === '1'){
      closeModal();
      return;
    }
  });

  // ESC cierra
  document.addEventListener('keydown', function(e){
    if(e.key === 'Escape' && modal.classList.contains('is-open')){
      closeModal();
    }
  });
})();
</script>
<script>
(function(){
  const btnPrint = document.getElementById('btnPrintBarcode');
  const imgEl = document.getElementById('barcodeImg');
  const codeEl = document.getElementById('barcodeSub');

  if(btnPrint){
    btnPrint.addEventListener('click', function(){
      const src = imgEl ? imgEl.src : '';
      const codeTxt = codeEl ? codeEl.textContent : '';

      if(!src) return;

      // Ventana de impresión (solo barcode)
      const w = window.open('', '_blank', 'width=800,height=600');
      w.document.open();
      w.document.write(`
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Imprimir Barcode</title>
<style>
  @page { margin: 12mm; }
  body { margin:0; font-family: Arial, sans-serif; }
  .wrap{
    width:100%;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:10px;
    padding-top:10mm;
  }
  .card{
    background:#fff;
    border:1px solid #ddd;
    border-radius:10px;
    padding:10mm;
    display:flex;
    align-items:center;
    justify-content:center;
  }
  img{
    width:140mm;     /* tamaño “mediano” impreso */
    max-width:100%;
    height:auto;
  }
  .code{
    font-size:12pt;
    color:#111;
  }
  /* ocultar todo lo que no sea impresión */
  @media print {
    body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
  }
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">
      <img src="${src}" alt="barcode">
    </div>
    ${codeTxt ? `<div class="code">${codeTxt}</div>` : ``}
  </div>
</body>
</html>
      `);
      w.document.close();

      // Esperar a que cargue la imagen y mandar a imprimir
      const img = w.document.querySelector('img');
      img.onload = () => {
        w.focus();
        w.print();
        w.close();
      };
    });
  }
})();
</script>