<?php
$config = require __DIR__ . '/../../../config.php';
$baseUrl = $config['app']['base_url'];

$labels = $labels ?? [];
$selectedMap = $selectedMap ?? [];

$cols = isset($cols) ? (int)$cols : 4;
$rows = isset($rows) ? (int)$rows : 3;
$totalSlots = isset($totalSlots) ? (int)$totalSlots : ($cols * $rows);

if ($cols < 1) $cols = 4;
if ($rows < 1) $rows = 3;
if ($totalSlots < 1) $totalSlots = $cols * $rows;

function eLabel($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function labelAssetUrl($path, $baseUrl) {
    $path = trim((string)$path);

    if ($path === '') return '';
    if (preg_match('/^https?:\/\//i', $path)) return $path;
    if (substr($path, 0, 1) === '/') return $path;

    return rtrim($baseUrl, '/') . '/' . ltrim($path, '/');
}

$labelsByKey = [];

foreach ($labels as $row) {
    $isVariant = !empty($row['variant_id']);

    $productId = $row['product_id']
        ?? $row['id_product']
        ?? $row['idProducto']
        ?? $row['id']
        ?? 0;

    $key = $isVariant
        ? ('v_' . (int)$row['variant_id'])
        : ('p_' . (int)$productId);

    if ((int)$productId > 0 || $isVariant) {
        $labelsByKey[$key] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Hoja de etiquetas</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
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

@page {
    size: letter portrait;
    margin: 0;
}

.no-print {
    padding: 12px;
    background: #111;
    color: #fff;
    display: flex;
    gap: 10px;
    align-items: center;
    justify-content: center;
}

.no-print button {
    border: 0;
    background: #ff4d6d;
    color: #fff;
    font-weight: 800;
    border-radius: 10px;
    padding: 10px 16px;
    cursor: pointer;
}

.no-print span {
    font-size: 13px;
    opacity: .8;
}

.sheet {
    width: 210mm;
    height: 270mm;
    margin: 0 auto;

    padding-top: 10mm;
    padding-right: 10mm;
    padding-bottom: 10mm;
    padding-left: 10mm;

    display: grid;
    grid-template-columns: repeat(<?= $cols ?>, 1fr);
    grid-template-rows: repeat(<?= $rows ?>, 1fr);

    column-gap: 8mm;
    row-gap: 8mm;

    background: #fff;
}

.slot {
    width: 100%;
    height: 100%;
    overflow: hidden;

    display: flex;
    align-items: center;
    justify-content: center;
}

.label {
    width: 100%;
    height: 100%;

    padding: 3mm 2mm;

    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;

    text-align: center;
    overflow: hidden;
}

.barcode-wrap {
    width: 100%;
    height: 15mm;

    display: flex;
    align-items: center;
    justify-content: center;

    overflow: hidden;
}

.barcode-wrap img {
    max-width: 100%;
    max-height: 15mm;
    object-fit: contain;
}

.barcode-text {
    width: 100%;
    font-family: monospace;
    font-size: 6.5pt;
    line-height: 1;
    margin-top: .8mm;
    word-break: break-word;
}

.product-name {
    width: 100%;
    margin-top: 1mm;
    font-size: 7pt;
    font-weight: 800;
    line-height: 1.05;
    text-transform: uppercase;
    text-align: center;
    max-height: 8mm;
    overflow: hidden;
    word-break: break-word;
}

.variant-text {
    width: 100%;
    margin-top: .7mm;
    font-size: 7pt;
    font-weight: 800;
    line-height: 1;
    text-transform: uppercase;
    text-align: center;
}

@media print {
    .no-print {
        display: none !important;
    }

    html,
    body {
        width: 210mm;
        height: 270mm;
        margin: 0;
        padding: 0;
    }

    .sheet {
        width: 210mm;
        height: 270mm;
        margin: 0;

        padding-top: 10mm;
        padding-right: 10mm;
        padding-bottom: 10mm;
        padding-left: 10mm;

        column-gap: 8mm;
        row-gap: 8mm;

        page-break-after: avoid;
        page-break-inside: avoid;
    }
}
</style>
</head>

<body>

<div class="no-print">
    <button type="button" onclick="window.print()">Imprimir</button>
    <span>Imprime en vertical, escala 100%, márgenes ninguno.</span>
</div>

<div class="sheet">
    <?php for ($slot = 1; $slot <= $totalSlots; $slot++): ?>
        <?php
            $key = $selectedMap[$slot] ?? '';
            $row = $labelsByKey[$key] ?? null;
        ?>

        <div class="slot">
            <?php if ($row): ?>
                <?php
                    $barcode = (string)($row['barcode'] ?? '');
                    $barcodePath = labelAssetUrl($row['barcode_path'] ?? '', $baseUrl);
                    $size = trim((string)($row['size'] ?? ''));
                ?>

                <div class="label">
                    <?php if ($barcodePath !== ''): ?>
                        <div class="barcode-wrap">
                            <img src="<?= eLabel($barcodePath) ?>" alt="Barcode">
                        </div>
                    <?php endif; ?>

                    <?php if ($barcode !== ''): ?>
                        <div class="barcode-text"><?= eLabel($barcode) ?></div>
                    <?php endif; ?>

                    <div class="product-name">
                        <?= eLabel($row['name'] ?? '') ?>
                    </div>

                    <?php if ($size !== ''): ?>
                        <div class="variant-text">
                            <?= eLabel($size) ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endfor; ?>
</div>

<script>
window.addEventListener('load', function(){
    setTimeout(function(){
        window.print();
    }, 450);
});
</script>

</body>
</html>