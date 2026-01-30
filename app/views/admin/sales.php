<?php
$success  = $success  ?? false;
$error    = $error    ?? '';
$products = $products ?? [];
$sales    = $sales    ?? [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Registrar ventas - El Pinito</title>
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

    .wrapper {
        display:flex;
        gap:20px;
        padding:20px;
        flex-wrap:wrap;
    }
    .panel {
        background:#1b1d22;
        border-radius:12px;
        padding:15px;
        box-shadow:0 0 10px rgba(0,0,0,0.5);
    }
    .panel-left { flex:1 1 35%; min-width:280px; }
    .panel-right { flex:2 1 55%; min-width:340px; }

    .title-panel { margin-top:0; margin-bottom:10px; font-size:16px; }

    .form-group { margin-bottom:10px; }
    label { font-size:13px; display:block; margin-bottom:3px; }
    input[type="number"],
    select,
    textarea {
        width:100%;
        padding:8px;
        border-radius:6px;
        border:1px solid #3a3c45;
        background:#101216;
        color:#eee;
        font-size:13px;
        resize:vertical;
    }
    textarea { min-height:60px; }

    button.primary {
        background:#25D366;
        color:#000;
        border:none;
        border-radius:8px;
        padding:9px 14px;
        cursor:pointer;
        font-weight:bold;
        font-size:13px;
        width:100%;
    }
    button.primary:hover { filter:brightness(1.05); }

    .msg-ok  { background:#143820; color:#9cffb2; padding:8px 10px; border-radius:6px; font-size:12px; margin-bottom:10px; }
    .msg-err { background:#3a1111; color:#ff9a9a; padding:8px 10px; border-radius:6px; font-size:12px; margin-bottom:10px; }

    table { width:100%; border-collapse:collapse; font-size:13px; }
    th, td { padding:8px; border-bottom:1px solid #2a2c33; }
    th { text-align:left; background:#24262d; position:sticky; top:0; z-index:1; }
    tr:nth-child(even) { background:#181a1f; }

    .chip {
        display:inline-block;
        padding:3px 8px;
        border-radius:999px;
        background:#24262d;
        font-size:11px;
    }

    .small-note {
        font-size:11px;
        color:#aaa;
        margin-top:4px;
    }
</style>
</head>
<body>

<div class="topbar">
  <h1>Registrar ventas</h1>
  <nav>
    <a href="index.php" target="_blank">Ver catálogo público</a>
    <a href="index.php?page=admin_products">Administrar productos</a>
    <a href="index.php?page=auth&action=logout" onclick="return confirm('¿Cerrar sesión?');">Cerrar sesión</a>
  </nav>
</div>

<div class="wrapper">

  <!-- FORMULARIO DE VENTA -->
  <div class="panel panel-left">
    <h2 class="title-panel">Nueva venta</h2>

    <?php if ($success): ?>
      <div class="msg-ok">Venta registrada y stock actualizado.</div>
    <?php endif; ?>
    <?php if ($error): ?>
      <div class="msg-err"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="post" action="index.php?page=admin_sales&action=save">
      <div class="form-group">
        <label>Producto</label>
        <select name="product_id" required>
          <option value="">-- Selecciona --</option>
          <?php foreach ($products as $p): ?>
            <option value="<?php echo $p['id']; ?>">
              <?php
                echo htmlspecialchars($p['name']);
                echo " (stock: " . (int)$p['stock'] . ")";
              ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label>Cantidad</label>
        <input type="number" name="quantity" min="1" required>
      </div>

      <div class="form-group">
        <label>Nota (opcional)</label>
        <textarea name="note" placeholder="Ej. Venta en efectivo, cliente recurrente..."></textarea>
      </div>

      <button type="submit" class="primary">Registrar venta</button>

      <div class="small-note">
        * Al registrar, se descuenta la cantidad del stock del producto.
      </div>
    </form>
  </div>

  <!-- LISTADO DE VENTAS -->
  <div class="panel panel-right">
    <h2 class="title-panel">Historial de ventas</h2>

    <div style="max-height:420px; overflow:auto; border-radius:8px;">
      <table>
        <tr>
          <th>ID</th>
          <th>Fecha</th>
          <th>Producto</th>
          <th>Cantidad</th>
          <th>Importe aprox.</th>
          <th>Nota</th>
        </tr>

        <?php if (!empty($sales)): ?>
          <?php foreach ($sales as $s): ?>
            <tr>
              <td><?php echo $s['id']; ?></td>
              <td><?php echo $s['date']; ?></td>
              <td><?php echo htmlspecialchars($s['product_name']); ?></td>
              <td><?php echo (int)$s['quantity']; ?></td>
              <td>
                <?php
                  $total = $s['quantity'] * $s['product_price'];
                  echo '$' . number_format($total, 2);
                ?>
              </td>
              <td>
                <?php if ($s['note']): ?>
                  <span class="chip"><?php echo htmlspecialchars($s['note']); ?></span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="6">No hay ventas registradas.</td></tr>
        <?php endif; ?>

      </table>
    </div>
  </div>

</div>

</body>
</html>