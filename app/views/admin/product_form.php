<?php
$success     = $success     ?? false;
$error       = $error       ?? '';
$editProduct = $editProduct ?? null;
$categories  = $categories  ?? [];
?>

<div class="panel panel-right">
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

  <form class="admin-form" action="?c=product&a=save" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?= htmlspecialchars($editProduct['id'] ?? '') ?>">
    <input type="hidden" name="current_image" value="<?= htmlspecialchars($editProduct['image_url'] ?? '') ?>">

    <div class="form-grid">
      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" required value="<?= htmlspecialchars($editProduct['name'] ?? '') ?>" placeholder="">
      </div>

      <div class="form-group">
        <label>Precio</label>
        <input type="number" step="0.01" name="price" required
               value="<?= isset($editProduct['price']) ? htmlspecialchars($editProduct['price']) : '' ?>" placeholder="0.00">
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

      <div class="form-group">
        <label>Stock</label>
        <input type="number" name="stock" min="0" required
               value="<?= isset($editProduct['stock']) ? (int)$editProduct['stock'] : 0 ?>">
      </div>

      <div class="form-group form-span-2">
        <label>Descripción (opcional)</label>
        <textarea name="description" rows="3" placeholder="Detalles, talla, color, etc."><?= htmlspecialchars($editProduct['description'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label>Orden en catálogo (opcional)</label>
        <input type="number" name="sort_order" min="0"
               value="<?= isset($editProduct['sort_order']) ? (int)$editProduct['sort_order'] : '' ?>" placeholder="Ej. 10">
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
        <input type="checkbox" name="active"
               <?= (!isset($editProduct['active']) || $editProduct['active']) ? 'checked' : '' ?>>
        <span>Producto activo</span>
      </div>
    </div>

    <div class="form-actions">
      <a class="btn-ghost" href="?c=product&a=index">Cancelar</a>
      <button type="submit" class="primary">Guardar producto</button>
    </div>
  </form>
</div>