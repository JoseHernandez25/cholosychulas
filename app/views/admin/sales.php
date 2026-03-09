<?php
$sales = $sales ?? [];
?>

<div class="panel panel-right form-panel product-form-panel">

<div class="panel-head">
<h2 class="title-panel">Ventas</h2>
</div>

<table>
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Teléfono</th>
<th>Piezas</th>
<th>Total</th>
<th>Status</th>
<th>Fecha</th>
<th></th>
</tr>
</thead>

<tbody>

<?php foreach($sales as $s): ?>

<tr>
<td><?= $s['id'] ?></td>

<td><?= htmlspecialchars($s['customer_name'] ?? 'Publico') ?></td>

<td><?= htmlspecialchars($s['customer_phone'] ?? '-') ?></td>

<td><?= $s['pieces'] ?></td>

<td>$<?= number_format($s['total'],2) ?></td>

<td><?= $s['status'] ?></td>

<td><?= $s['created_at'] ?></td>

<td>
<a class="btn-link" href="?c=sale&a=show&id=<?= $s['id'] ?>">Ver</a>
</td>

</tr>

<?php endforeach ?>

</tbody>
</table>
</div>