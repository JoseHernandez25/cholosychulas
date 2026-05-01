<?php
$sale = $sale ?? [];
$items = $items ?? [];
?>

<div class="panel panel-right form-panel product-form-panel">

<div class="panel-head">
  <h2 class="title-panel">Venta #<?= $sale['id'] ?></h2>

  <div style="display:flex; gap:10px; flex-wrap:wrap;">
    <a class="btn-ghost" href="?c=sale&a=index">← Volver a ventas</a>
    <a class="btn-primary" href="?c=sale&a=ticket&id=<?= $sale['id'] ?>" target="_blank">Imprimir ticket</a>
  </div>
</div>


<div class="sale-summary-grid">

<div class="sale-box">
<span class="sale-label">Cliente</span>
<strong><?= $sale['customer_name'] ?: 'Publico en general' ?></strong>
</div>

<div class="sale-box">
<span class="sale-label">Teléfono</span>
<strong><?= $sale['customer_phone'] ?: '-' ?></strong>
</div>

<div class="sale-box">
<span class="sale-label">Piezas</span>
<strong><?= $sale['pieces'] ?></strong>
</div>

<div class="sale-box">
<span class="sale-label">Total</span>
<strong>$<?= number_format($sale['total'],2) ?></strong>
</div>

</div>


<div class="table-wrap" style="margin-top:20px;">

<table>

<thead>
<tr>
<th>Producto</th>
<th>Cantidad</th>
<th>Precio</th>
<th>Subtotal</th>
</tr>
</thead>

<tbody>

<?php foreach($items as $i): ?>

<tr>

<td><?= htmlspecialchars($i['name']) ?></td>

<td><?= $i['qty'] ?></td>

<td>$<?= number_format($i['price'],2) ?></td>

<td>$<?= number_format($i['subtotal'],2) ?></td>

</tr>

<?php endforeach ?>

</tbody>

</table>

</div>

</div>