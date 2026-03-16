<?php
$success     = $success     ?? false;
$error       = $error       ?? '';
$editProduct = $editProduct ?? null;
$categories  = $categories  ?? [];
$variants    = $variants    ?? [];

$hasVariants = !empty($editProduct['has_variants']);
?>

<div class="panel panel-right form-panel product-form-panel">
  <div class="panel-head">
    <div>
      <h2 class="title-panel"><?= $editProduct ? 'Editar producto' : 'Registrar producto' ?></h2>
      <div class="panel-sub">Captura datos del producto para catálogo y caja.</div>
    </div>

    <a class="btn-ghost" href="?c=product&a=index">← Volver a existencias</a>
  </div>

  <?php if ($success): ?>
    <div class="msg-ok">Producto guardado correctamente.</div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <form class="admin-form" action="?c=product&a=save" method="POST" enctype="multipart/form-data" id="productForm">
    <input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? '') ?>">
    <input type="hidden" name="current_image" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">

    <div class="form-grid">
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>">
      </div>

      <div class="form-group">
        <label>Categoría</label>
        <select name="category_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($categories as $c): ?>
            <?php $selected = (isset($editProduct['category_id']) && (int)$editProduct['category_id'] === (int)$c['id']) ? 'selected' : ''; ?>
            <option value="<?= (int)$c['id'] ?>" <?= $selected ?>>
              <?= htmlspecialchars($c['name']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group form-span-2">
        <label class="variant-toggle">
          <input type="checkbox" name="has_variants" id="has_variants" value="1" <?= $hasVariants ? 'checked' : '' ?>>
          <span>Este producto tiene variantes (talla, color, etc.)</span>
        </label>
      </div>

      <div class="form-group" id="general-price-wrap">
        <label>Precio general</label>
        <input
          type="number"
          step="0.01"
          name="price"
          value="<?= isset($editProduct['price']) ? htmlspecialchars($editProduct['price']) : '' ?>"
          placeholder="0.00"
        >
        <small class="help-text">Para productos simples o como precio base.</small>
      </div>

      <div class="form-group" id="general-stock-wrap">
        <label>Stock general</label>
        <input
          type="number"
          name="stock"
          min="0"
          value="<?= isset($editProduct['stock']) ? (int)$editProduct['stock'] : 0 ?>"
        >
        <small class="help-text">Solo se usa en productos sin variantes.</small>
      </div>

      <div class="form-group form-span-2">
        <label>Descripción (opcional)</label>
        <textarea name="description" rows="3" placeholder="Detalles, materiales, notas, etc."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Orden en catálogo (opcional)</label>
        <input
          type="number"
          name="sort_order"
          min="0"
          value="<?= isset($editProduct['sort_order']) ? (int)$editProduct['sort_order'] : '' ?>"
          placeholder="Ej. 10"
        >
      </div>

      <div class="form-group">
        <label>Imagen</label>
        <div class="filebox">
          <input type="file" name="image" accept="image/*">
          <small>Deja vacío para no cambiar</small>
        </div>
      </div>

      <?php if (!empty($editProduct['image_url'])): ?>
        <div class="form-group form-span-2">
          <label>Imagen actual</label>
          <img src="<?= htmlspecialchars($editProduct['image_url']) ?>" class="thumb" alt="Imagen actual">
        </div>
      <?php endif; ?>

      <div class="form-group form-span-2 checkbox-inline">
        <input
          type="checkbox"
          name="active"
          <?= (!isset($editProduct['active']) || $editProduct['active']) ? 'checked' : '' ?>
        >
        <span>Producto activo</span>
      </div>
    </div>

    <div id="variants-block" class="variants-block" style="<?= $hasVariants ? '' : 'display:none;' ?>">
      <div class="variants-head">
        <div>
          <h3>Variantes</h3>
          <p>Agrega combinaciones como talla, color, precio y stock.</p>
        </div>
        <button type="button" class="btn-ghost" id="add-variant-btn">+ Agregar variante</button>
      </div>

      <div class="table-responsive">
        <table class="variants-table" id="variants-table">
          <thead>
            <tr>
              <th>Talla</th>
              <th>Color</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Activa</th>
              <th>Quitar</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($variants)): ?>
              <?php foreach ($variants as $i => $v): ?>
                <tr>
                  <td>
                    <input type="hidden" name="variants[<?= $i ?>][id]" value="<?= (int)($v['id'] ?? 0) ?>">
                    <input
                      type="text"
                      name="variants[<?= $i ?>][size]"
                      value="<?= htmlspecialchars($v['size'] ?? '') ?>"
                      placeholder="Ej. CH, M, G, 32"
                    >
                  </td>
                  <td>
                    <input
                      type="text"
                      name="variants[<?= $i ?>][color]"
                      value="<?= htmlspecialchars($v['color'] ?? '') ?>"
                      placeholder="Ej. Negro"
                    >
                  </td>
                  <td>
                    <input
                      type="number"
                      step="0.01"
                      name="variants[<?= $i ?>][price]"
                      value="<?= htmlspecialchars($v['price'] ?? '') ?>"
                      placeholder="0.00"
                    >
                  </td>
                  <td>
                    <input
                      type="number"
                      min="0"
                      name="variants[<?= $i ?>][stock]"
                      value="<?= htmlspecialchars((string)($v['stock'] ?? 0)) ?>"
                    >
                  </td>
                  <td class="cell-center">
                    <input
                      type="checkbox"
                      name="variants[<?= $i ?>][active]"
                      value="1"
                      <?= !isset($v['active']) || !empty($v['active']) ? 'checked' : '' ?>
                    >
                  </td>
                  <td class="cell-center">
                    <button type="button" class="btn-remove-variant">✕</button>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php else: ?>
              <tr>
                <td>
                  <input type="text" name="variants[0][size]" placeholder="Ej. CH, M, G, 32">
                </td>
                <td>
                  <input type="text" name="variants[0][color]" placeholder="Ej. Negro">
                </td>
                <td>
                  <input type="number" step="0.01" name="variants[0][price]" placeholder="0.00">
                </td>
                <td>
                  <input type="number" min="0" name="variants[0][stock]" value="0">
                </td>
                <td class="cell-center">
                  <input type="checkbox" name="variants[0][active]" value="1" checked>
                </td>
                <td class="cell-center">
                  <button type="button" class="btn-remove-variant">✕</button>
                </td>
              </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn-ghost" href="?c=product&a=index">Cancelar</a>
      <button type="submit" class="primary">Guardar producto</button>
    </div>
  </form>
</div>

<style>
  .variant-toggle{
    display:flex;
    align-items:center;
    gap:10px;
    font-weight:600;
    margin-top:6px;
  }

  .help-text{
    display:block;
    margin-top:6px;
    font-size:.85rem;
    opacity:.8;
  }

  .variants-block{
    margin-top:22px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    padding:16px;
    background:rgba(255,255,255,.02);
  }

  .variants-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:14px;
    margin-bottom:14px;
  }

  .variants-head h3{
    margin:0 0 4px;
    font-size:1.05rem;
  }

  .variants-head p{
    margin:0;
    opacity:.8;
    font-size:.92rem;
  }

  .table-responsive{
    width:100%;
    overflow-x:auto;
  }

  .variants-table{
    width:100%;
    border-collapse:collapse;
    min-width:760px;
  }

  .variants-table th,
  .variants-table td{
    padding:10px 8px;
    border-bottom:1px solid rgba(255,255,255,.08);
    vertical-align:middle;
  }

  .variants-table th{
    text-align:left;
    font-size:.9rem;
    opacity:.85;
  }

  .variants-table input[type="text"],
  .variants-table input[type="number"]{
    width:100%;
    min-width:100px;
    padding:10px 12px;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.03);
    color:inherit;
    outline:none;
  }

  .cell-center{
    text-align:center;
  }

  .btn-remove-variant{
    border:none;
    border-radius:10px;
    padding:8px 12px;
    cursor:pointer;
    background:#842029;
    color:#fff;
    font-weight:700;
  }

  .btn-remove-variant:hover{
    opacity:.9;
  }

  .variants-note{
    margin-top:12px;
    font-size:.88rem;
    opacity:.8;
  }

  @media (max-width: 768px){
    .variants-head{
      flex-direction:column;
      align-items:stretch;
    }
  }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
  const hasVariants = document.getElementById('has_variants');
  const variantsBlock = document.getElementById('variants-block');
  const generalStockWrap = document.getElementById('general-stock-wrap');
  const addVariantBtn = document.getElementById('add-variant-btn');
  const variantsTableBody = document.querySelector('#variants-table tbody');

  function toggleVariantMode() {
    const enabled = hasVariants.checked;
    variantsBlock.style.display = enabled ? 'block' : 'none';
    generalStockWrap.style.display = enabled ? 'none' : 'block';
  }

  function getNextIndex() {
    return variantsTableBody.querySelectorAll('tr').length;
  }

  function addVariantRow(data = {}) {
    const index = getNextIndex();

    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>
        <input type="text" name="variants[${index}][size]" placeholder="Ej. CH, M, G, 32" value="${data.size ?? ''}">
      </td>
      <td>
        <input type="text" name="variants[${index}][color]" placeholder="Ej. Negro" value="${data.color ?? ''}">
      </td>
      <td>
        <input type="number" step="0.01" name="variants[${index}][price]" placeholder="0.00" value="${data.price ?? ''}">
      </td>
      <td>
        <input type="number" min="0" name="variants[${index}][stock]" value="${data.stock ?? 0}">
      </td>
      <td class="cell-center">
        <input type="checkbox" name="variants[${index}][active]" value="1" ${data.active === false ? '' : 'checked'}>
      </td>
      <td class="cell-center">
        <button type="button" class="btn-remove-variant">✕</button>
      </td>
    `;
    variantsTableBody.appendChild(tr);
  }

  hasVariants.addEventListener('change', toggleVariantMode);

  addVariantBtn.addEventListener('click', function () {
    addVariantRow();
  });

  document.addEventListener('click', function (e) {
    if (e.target.classList.contains('btn-remove-variant')) {
      const rows = variantsTableBody.querySelectorAll('tr');

      if (rows.length === 1) {
        const inputs = rows[0].querySelectorAll('input[type="text"], input[type="number"]');
        inputs.forEach(input => {
          if (input.type === 'number') {
            input.value = input.name.includes('[stock]') ? 0 : '';
          } else {
            input.value = '';
          }
        });

        const hiddenId = rows[0].querySelector('input[type="hidden"][name*="[id]"]');
        if (hiddenId) hiddenId.value = '';

        const checks = rows[0].querySelectorAll('input[type="checkbox"]');
        checks.forEach(chk => chk.checked = true);
        return;
      }

      e.target.closest('tr').remove();
    }
  });

  toggleVariantMode();
});
</script>