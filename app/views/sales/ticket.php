<?php
$config = require __DIR__ . '/../../../config.php';
$baseUrl = $config['app']['base_url'];

$sale  = $sale ?? [];
$items = $items ?? [];

function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$fechaTicket = '';

if (!empty($sale['created_at'])) {
    $fechaTicket = date('d/m/Y h:i A', strtotime($sale['created_at']));
    $fechaTicket = str_replace(['AM', 'PM'], ['a. m.', 'p. m.'], $fechaTicket);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ticket Venta #<?= (int)$sale['id'] ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
    @font-face {
        font-family: 'Chicanos';
        src: url('<?= $baseUrl ?>assets/fonts/ChicanosPersonalUseRegular-qZDw5.woff2') format('woff2');
        font-weight: normal;
        font-style: normal;
    }

    .brand {
        font-family: 'Chicanos', cursive;
        font-size: 24px;
        text-align: center;
        margin-bottom: 8px;
        line-height: 1;
    }

    * {
        box-sizing: border-box;
    }

    html,
    body {
        margin: 0;
        padding: 0;
        background: #fff;
        color: #000;
        font-family: Arial, Helvetica, sans-serif;
    }

    .ticket {
        width: 46mm;
        max-width: 46mm;
        margin: 0;
        padding: 5px 2mm 8px;
        overflow: hidden;
    }

    .center {
        text-align: center;
    }

    .sub {
        font-size: 11px;
        margin-bottom: 8px;
        line-height: 1.2;
    }

    .line {
        border-top: 1px dashed #000;
        margin: 7px 0;
    }

    .meta,
    .totals {
        width: 100%;
        border-collapse: collapse;
        font-size: 10.5px;
        line-height: 1.3;
    }

    .meta tr,
    .totals tr {
        display: block;
        margin-bottom: 4px;
    }

    .meta td,
    .totals td {
        display: block;
        width: 100% !important;
        padding: 1px 0;
        text-align: left !important;
        white-space: normal !important;
        word-break: break-word;
    }

    .items {
        width: 100%;
        border-collapse: collapse;
        table-layout: fixed;
        font-size: 10.5px;
        line-height: 1.3;
    }

    .items thead {
        display: none;
    }

    .items tr {
        display: block;
        margin-bottom: 7px;
    }

    .items th,
    .items td {
        display: block;
        width: 100% !important;
        padding: 1px 0;
        text-align: left !important;
        white-space: normal !important;
    }

    .prod {
        font-weight: 700;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .muted {
        color: #444;
        font-size: 10px;
        line-height: 1.25;
    }

    .price-line {
        font-size: 10.5px;
        font-weight: 700;
        margin-top: 2px;
    }

    .footer {
        text-align: center;
        font-size: 10.5px;
        margin-top: 8px;
        line-height: 1.25;
    }

    .no-print {
        width: 46mm;
        max-width: 46mm;
        margin: 10px 0 0;
        padding: 0 2mm;
    }

    .print-btn {
        width: 100%;
        border: 1px solid #111;
        background: #111;
        color: #fff;
        border-radius: 8px;
        padding: 9px 10px;
        cursor: pointer;
        font-size: 13px;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        @page {
            margin: 0;
            size: 58mm auto;
        }

        html,
        body {
            width: 58mm;
            margin: 0;
            padding: 0;
        }

        .ticket {
            width: 46mm;
            max-width: 46mm;
            margin: 0;
            padding: 5px 2mm 8px;
            overflow: hidden;
        }
    }
</style>
</head>

<body>

<div class="no-print">
    <button class="print-btn" onclick="window.print()">Imprimir ticket</button>
</div>

<div class="ticket">
    <div class="center">
        <div class="brand">Cholos y Chulas</div>
        <div class="sub">Ticket de venta</div>
    </div>

    <div class="line"></div>

    <table class="meta">
        <tr>
            <td><strong>Venta:</strong></td>
            <td>#<?= (int)$sale['id'] ?></td>
        </tr>
        <tr>
            <td><strong>Fecha:</strong></td>
            <td><?= e($fechaTicket) ?></td>
        </tr>
        <tr>
            <td><strong>Status:</strong></td>
            <td><?= e($sale['status'] ?? '') ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <div><strong>Producto</strong></div>
    <div class="line"></div>

    <table class="items">
        <tbody>
            <?php foreach ($items as $i): ?>
                <tr>
                    <td>
                        <div class="prod"><?= e($i['name']) ?></div>
                        <div class="muted">
                            <?= (float)$i['qty'] ?> x $<?= number_format((float)$i['price'], 2) ?>
                        </div>
                        <div class="price-line">
                            Importe: $<?= number_format((float)$i['subtotal'], 2) ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="line"></div>

    <table class="totals">
        <tr>
            <td><strong>Piezas:</strong></td>
            <td><strong><?= (int)($sale['pieces'] ?? 0) ?></strong></td>
        </tr>
        <tr>
            <td><strong>Total:</strong></td>
            <td><strong>$<?= number_format((float)($sale['total'] ?? 0), 2) ?></strong></td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="footer">
        Gracias por tu compra
    </div>
</div>

<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 250);
});
</script>

</body>
</html>