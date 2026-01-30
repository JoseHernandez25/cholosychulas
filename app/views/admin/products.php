<?php
// app/views/admin/products.php
$success     = $success     ?? false;
$error       = $error       ?? '';
$editProduct = $editProduct ?? null;
$products    = $products    ?? [];
$categories  = $categories  ?? [];
$base_url    = $base_url    ?? ''; // por si tu layout lo usa
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Admin Productos</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
    body { margin:0; font-family:Arial, sans-serif; background:#0f1012; color:#eee; }

    .topbar {
        background:#15171b;
        padding:10px 20px;
        display:flex;
        align-items:center;
        justify-content:space-between;
        box-shadow:0 2px 4px rgba(0,0,0,0.4);
        position:sticky;
        top:0;
        z-index:10;
    }
    .topbar h1 { margin:0; font-size:18px; }
    .topbar nav a {
        color:#ddd;
        text-decoration:none;
        margin-left:15px;
        font-size:14px;
    }
    .topbar nav a:hover { color:#25D366; }

    .wrapper { display:flex; gap:20px; padding:20px; flex-wrap:wrap; }
    .panel { background:#1b1d22; border-radius:12px; padding:15px; box-shadow:0 0 10px rgba(0,0,0,0.5); }
    .panel-left { flex:2 1 55%; min-width:340px; }
    .panel-right { flex:1 1 35%; min-width:280px; }

    table { width:100%; border-collapse:collapse; font-size:13px; }
    th, td { padding:8px; border-bottom:1px solid #2a2c33; }
    th { text-align:left; background:#24262d; position:sticky; top:0; z-index:1; }
    tr:nth-child(even) { background:#181a1f; }

    .badge { padding:2px 7px; border-radius:999px; font-size:11px; display:inline-block; }
    .badge-active { background:#25D366; color:#000; }
    .badge-inactive { background:#a00; color:#fff; }

    .btn-link { color:#25D366; text-decoration:none; margin-right:8px; font-size:12px; }
    .btn-link:hover { text-decoration:underline; }

    .form-group { margin-bottom:10px; }
    label { font-size:13px; display:block; margin-bottom:3px; }
    input[type="text"], input[type="number"], select, textarea {
        width:100%;
        padding:8px;
        border-radius:6px;
        border:1px solid #3a3c45;
        background:#101216;
        color:#eee;
        font-size:13px;
    }
    input[type="file"] { width:100%; font-size:12px; color:#ccc; }

    .checkbox-inline { display:flex; align-items:center; gap:6px; font-size:13px; }
    button.primary {
        background:#25D366; color:#000; border:none; border-radius:8px;
        padding:9px 14px; cursor:pointer; font-weight:bold; font-size:13px;
    }
    button.primary:hover { filter:brightness(1.05); }

    .msg-ok  { background:#143820; color:#9cffb2; padding:8px 10px; border-radius:6px; font-size:12px; margin-bottom:10px; }
    .msg-err { background:#3a1111; color:#ff9a9a; padding:8px 10px; border-radius:6px; font-size:12px; margin-bottom:10px; }

    img.thumb { max-width:100%; max-height:120px; border-radius:8px; margin-bottom:8px; }
    .title-panel { margin-top:0; margin-bottom:10px; font-size:16px; }
</style>
</head>
<body>

<div class="topbar">
  <h1>Admin Productos</h1>
  <nav>
    <a href="index.php" target="_blank">Ver catálogo</a>
    <!-- si luego haces ventas: <a href="index.php?c=sale&a=index">Registrar ventas</a> -->
    <a href="index.php?c=product&a=index">Productos</a>
  </nav>
</div>

<div class="wrapper">

  <!-- LISTADO DE PRODUCTOS -->
  <div class="panel panel-left">
    <h2 class="title-panel">Productos</h2>

    <?php if ($success): ?>
      <div class="msg-ok">Producto guardado correctamente.</div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="msg-err"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div style="max-height:420px; overflow:auto; border-radius:8px;">
      <table>
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

        <?php if (!empty($products)): ?>
          <?php foreach ($products as $row): ?>
            <tr>
              <td><?php echo (int)$row['id']; ?></td>
              <td><?php echo htmlspecialchars($row['name']); ?></td>
              <td><?php echo htmlspecialchars($row['category'] ?? ''); ?></td>
              <td>$<?php echo number_format((float)$row['price'], 2); ?></td>
              <td><?php echo (int)($row['stock'] ?? 0); ?></td>

              <td>
                <?php echo htmlspecialchars($row['barcode'] ?? ''); ?>
                <?php if (!empty($row['barcode_path'])): ?>
                  <div style="margin-top:6px;">
                  <img src="<?= htmlspecialchars($row['barcode_path']); ?>"
                  style="width:420px; height:auto; background:#fff; padding:16px; border-radius:8px;">
                  </div>
                <?php endif; ?>
              </td>

              <td>
                <span class="badge <?php echo !empty($row['active']) ? 'badge-active':'badge-inactive'; ?>">
                  <?php echo !empty($row['active']) ? 'ACTIVO' : 'INACTIVO'; ?>
                </span>
              </td>

              <td>
                <a class="btn-link" href="index.php?c=product&a=index&edit=<?php echo (int)$row['id']; ?>">Editar</a>
                <a class="btn-link"
                   href="index.php?c=product&a=toggle&id=<?php echo (int)$row['id']; ?>"
                   onclick="return confirm('¿Cambiar estado de este producto?');">
                   <?php echo !empty($row['active']) ? 'Desactivar' : 'Activar'; ?>
                </a>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8">No hay productos registrados.</td></tr>
        <?php endif; ?>
      </table>
    </div>
  </div>

  <!-- FORMULARIO ALTA / EDICIÓN -->
  <div class="panel panel-right">
    <h2 class="title-panel"><?php echo $editProduct ? 'Editar producto' : 'Nuevo producto'; ?></h2>

    <form action="index.php?c=product&a=save" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="id" value="<?php echo htmlspecialchars($editProduct['id'] ?? ''); ?>">
      <input type="hidden" name="current_image" value="<?php echo htmlspecialchars($editProduct['image_url'] ?? ''); ?>">

      <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="name" required
               value="<?php echo htmlspecialchars($editProduct['name'] ?? ''); ?>">
      </div>

      <div class="form-group">
        <label>Precio</label>
        <input type="number" step="0.01" name="price" required
               value="<?php echo isset($editProduct['price']) ? htmlspecialchars($editProduct['price']) : ''; ?>">
      </div>

      <div class="form-group">
        <label>Categoría</label>
        <select name="category_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($categories as $c): ?>
            <?php
              $selected = (isset($editProduct['category_id']) && (int)$editProduct['category_id'] === (int)$c['id'])
                          ? 'selected' : '';
            ?>
            <option value="<?php echo (int)$c['id']; ?>" <?php echo $selected; ?>>
              <?php echo htmlspecialchars($c['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Stock</label>
        <input type="number" name="stock" min="0" required
              value="<?php echo isset($editProduct['stock']) ? (int)$editProduct['stock'] : 0; ?>">
      </div>

      <div class="form-group">
        <label>Descripción (opcional)</label>
        <textarea name="description" rows="3"><?php echo htmlspecialchars($editProduct['description'] ?? ''); ?></textarea>
      </div>

      <div class="form-group">
        <label>Orden en catálogo (opcional)</label>
        <input type="number" name="sort_order" min="0"
              value="<?php echo isset($editProduct['sort_order']) ? (int)$editProduct['sort_order'] : ''; ?>">
      </div>

      <div class="form-group">
        <label>Imagen (deja vacío para no cambiar)</label>
        <?php if (!empty($editProduct['image_url'])): ?>
          <img src="<?php echo htmlspecialchars($editProduct['image_url']); ?>" class="thumb" alt="Imagen actual">
        <?php endif; ?>
        <input type="file" name="image" accept="image/*">
      </div>

      <div class="form-group checkbox-inline">
        <input type="checkbox" name="active" <?php echo (!isset($editProduct['active']) || $editProduct['active']) ? 'checked' : ''; ?>>
        <span>Producto activo</span>
      </div>

      <button type="submit" class="primary">Guardar producto</button>
    </form>
  </div>

</div>

</body>
</html>