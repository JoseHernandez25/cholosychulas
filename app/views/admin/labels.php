<?php
$labels = $labels ?? [];

// categorías únicas para filtro
$categoryOptions = [];
foreach ($labels as $row) {
    $cat = trim((string)($row['category'] ?? ''));
    if ($cat !== '') {
        $categoryOptions[$cat] = $cat;
    }
}
ksort($categoryOptions);
?>

<div class="panel panel-right form-panel product-form-panel">
  <div class="panel-head">
    <div>
      <h2 class="title-panel">Etiquetas</h2>
      <div class="panel-sub">Imprime etiquetas de productos simples y variantes.</div>
    </div>
  </div>

  <div class="filters-card">
    <div class="filters-head">
      <h3>Filtros</h3>
      <button type="button" class="btn-ghost btn-sm" id="btnClearFilters">Limpiar</button>
    </div>

    <div class="filters-grid">
      <div class="form-group">
        <label for="filterName">Buscar</label>
        <input type="text" id="filterName" placeholder="Nombre del producto">
      </div>

      <div class="form-group">
        <label for="filterCategory">Categoría</label>
        <select id="filterCategory">
          <option value="">Todas</option>
          <?php foreach ($categoryOptions as $cat): ?>
            <option value="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="filterType">Tipo</label>
        <select id="filterType">
          <option value="">Todos</option>
          <option value="simple">Simple</option>
          <option value="variant">Variante</option>
        </select>
      </div>

      <div class="form-group">
        <label for="filterStock">Stock</label>
        <select id="filterStock">
          <option value="">Todos</option>
          <option value="con">Con stock</option>
          <option value="sin">Sin stock</option>
        </select>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table id="labelsTable">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th>Tipo</th>
          <th>Talla</th>
          <th>Color</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Barcode</th>
          <th>Copias</th>
          <th>Acción</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($labels)): ?>
          <?php foreach ($labels as $row): ?>
            <?php
              $isVariant = !empty($row['variant_id']);
              $typeValue = $isVariant ? 'variant' : 'simple';
              $stockVal  = (int)($row['stock'] ?? 0);
            ?>
            <tr
              data-name="<?= htmlspecialchars(mb_strtolower((string)$row['name'])) ?>"
              data-category="<?= htmlspecialchars(mb_strtolower((string)($row['category'] ?? ''))) ?>"
              data-type="<?= $typeValue ?>"
              data-stock="<?= $stockVal > 0 ? 'con' : 'sin' ?>"
              data-barcode="<?= htmlspecialchars($row['barcode'] ?? '') ?>"
              data-barcode-path="<?= htmlspecialchars($row['barcode_path'] ?? '') ?>"
            >
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><?= htmlspecialchars($row['category'] ?? '') ?></td>
              <td>
                <span class="badge <?= $isVariant ? 'badge-variant' : 'badge-simple' ?>">
                  <?= $isVariant ? 'VARIANTE' : 'SIMPLE' ?>
                </span>
              </td>
              <td><?= htmlspecialchars($row['size'] ?? '—') ?></td>
              <td><?= htmlspecialchars($row['color'] ?? '—') ?></td>
              <td>$<?= number_format((float)($row['price'] ?? 0), 2) ?></td>
              <td><?= $stockVal ?></td>
              <td>
                <?php if (!empty($row['barcode'])): ?>
                  <span class="barcode-txt"><?= htmlspecialchars($row['barcode']) ?></span>
                <?php else: ?>
                  <span class="muted">—</span>
                <?php endif; ?>
              </td>
              <td>
                <input type="number" class="copies-input" min="1" value="1">
              </td>
              <td>
                <button type="button" class="btn-primary btn-print-one">Imprimir</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr id="emptyRowStatic">
            <td colspan="10">No hay etiquetas disponibles.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="pagination-wrap" id="paginationWrap" style="display:none;">
    <button type="button" class="btn-ghost btn-sm" id="btnPrevPage">← Anterior</button>
    <span class="pagination-info" id="paginationInfo">Página 1 de 1</span>
    <button type="button" class="btn-ghost btn-sm" id="btnNextPage">Siguiente →</button>
  </div>

  <div id="noResults" class="empty-filters" style="display:none;">
    No se encontraron resultados con esos filtros.
  </div>
</div>

<style>
  .filters-card{
    margin:14px 0 18px;
    padding:16px;
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    background:rgba(255,255,255,.025);
    box-shadow:0 8px 24px rgba(0,0,0,.14);
  }

  .filters-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:14px;
  }

  .filters-head h3{
    margin:0;
    font-size:1rem;
    color:#fff;
  }

  .filters-grid{
    display:grid;
    grid-template-columns:repeat(4, minmax(0, 1fr));
    gap:14px;
  }

  .filters-grid .form-group label{
    display:block;
    margin-bottom:6px;
    font-size:.88rem;
    font-weight:600;
    opacity:.92;
    color:#e9e9ea;
  }

  .filters-grid input,
  .filters-grid select{
    width:100%;
    height:44px;
    min-height:44px;
    padding:0 14px;
    border-radius:12px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.045);
    color:#f5f5f5;
    outline:none;
    box-sizing:border-box;
    line-height:44px;
    transition:border-color .18s ease, box-shadow .18s ease, background .18s ease;
  }

  .filters-grid input::placeholder{
    color:rgba(255,255,255,.45);
  }

  .filters-grid input:hover,
  .filters-grid select:hover{
    background:rgba(255,255,255,.06);
    border-color:rgba(255,255,255,.16);
  }

  .filters-grid input:focus,
  .filters-grid select:focus{
    border-color:rgba(255,255,255,.28);
    box-shadow:0 0 0 3px rgba(255,255,255,.06);
    background:rgba(255,255,255,.07);
  }

  .filters-grid select{
    appearance:none;
    -webkit-appearance:none;
    -moz-appearance:none;
    padding-right:42px;
    cursor:pointer;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='18' height='18' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    background-size:16px;
  }

  .filters-grid select option{
    background:#1b1c20;
    color:#f5f5f5;
  }

  .badge-simple{
    background: rgba(13, 110, 253, .15);
    color: #7db1ff;
    border: 1px solid rgba(13, 110, 253, .25);
    padding:4px 8px;
    border-radius:999px;
    font-size:.78rem;
    font-weight:700;
  }

  .badge-variant{
    background: rgba(255, 193, 7, .12);
    color: #ffd666;
    border: 1px solid rgba(255, 193, 7, .2);
    padding:4px 8px;
    border-radius:999px;
    font-size:.78rem;
    font-weight:700;
  }

  .copies-input{
    width:72px;
    height:38px;
    text-align:center;
    border-radius:10px;
    border:1px solid rgba(255,255,255,.10);
    background:rgba(255,255,255,.04);
    color:#fff;
    outline:none;
  }

  .copies-input:focus{
    border-color:rgba(255,255,255,.28);
    box-shadow:0 0 0 3px rgba(255,255,255,.06);
  }

  .barcode-txt{
    font-family:monospace;
    font-size:.9rem;
  }

  .btn-sm{
    padding:8px 12px;
    font-size:.9rem;
  }

  .empty-filters{
    margin-top:14px;
    padding:14px 16px;
    border-radius:12px;
    background: rgba(255,255,255,.03);
    border:1px dashed rgba(255,255,255,.10);
    opacity:.9;
  }

  .pagination-wrap{
    margin-top:14px;
    display:flex;
    align-items:center;
    justify-content:center;
    gap:12px;
    flex-wrap:wrap;
  }

  .pagination-info{
    font-size:.92rem;
    color:#ddd;
  }

  @media (max-width: 980px){
    .filters-grid{
      grid-template-columns:repeat(2, minmax(0, 1fr));
    }
  }

  @media (max-width: 640px){
    .filters-grid{
      grid-template-columns:1fr;
    }
  }
</style>

<script>
(function(){
  const filterName = document.getElementById('filterName');
  const filterCategory = document.getElementById('filterCategory');
  const filterType = document.getElementById('filterType');
  const filterStock = document.getElementById('filterStock');
  const btnClear = document.getElementById('btnClearFilters');
  const tbody = document.querySelector('#labelsTable tbody');
  const noResults = document.getElementById('noResults');

  const paginationWrap = document.getElementById('paginationWrap');
  const btnPrevPage = document.getElementById('btnPrevPage');
  const btnNextPage = document.getElementById('btnNextPage');
  const paginationInfo = document.getElementById('paginationInfo');

  const rowsPerPage = 5;
  let currentPage = 1;
  let filteredRows = [];

  function normalize(value){
    return (value || '').toString().trim().toLowerCase();
  }

  function getVisibleRowsByFilter() {
    if (!tbody) return [];

    const nameVal = normalize(filterName.value);
    const categoryVal = normalize(filterCategory.value);
    const typeVal = normalize(filterType.value);
    const stockVal = normalize(filterStock.value);

    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.id !== 'emptyRowStatic');

    return rows.filter(row => {
      const rowName = normalize(row.getAttribute('data-name'));
      const rowCategory = normalize(row.getAttribute('data-category'));
      const rowType = normalize(row.getAttribute('data-type'));
      const rowStock = normalize(row.getAttribute('data-stock'));

      if (nameVal && !rowName.includes(nameVal)) return false;
      if (categoryVal && rowCategory !== categoryVal) return false;
      if (typeVal && rowType !== typeVal) return false;
      if (stockVal && rowStock !== stockVal) return false;

      return true;
    });
  }

  function renderPagination() {
    const totalRows = filteredRows.length;
    const totalPages = Math.max(1, Math.ceil(totalRows / rowsPerPage));

    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;

    const pageRows = filteredRows.slice(start, end);

    const allRows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.id !== 'emptyRowStatic');
    allRows.forEach(row => row.style.display = 'none');

    pageRows.forEach(row => row.style.display = '');

    noResults.style.display = totalRows === 0 ? 'block' : 'none';
    paginationWrap.style.display = totalRows > 0 ? 'flex' : 'none';
    paginationInfo.textContent = `Página ${currentPage} de ${totalPages}`;

    btnPrevPage.disabled = currentPage <= 1;
    btnNextPage.disabled = currentPage >= totalPages;
  }

  function applyFilters(resetPage = true){
    if (resetPage) currentPage = 1;
    filteredRows = getVisibleRowsByFilter();
    renderPagination();
  }

  [filterName, filterCategory, filterType, filterStock].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => applyFilters(true));
    el.addEventListener('change', () => applyFilters(true));
  });

  if (btnClear) {
    btnClear.addEventListener('click', function(){
      filterName.value = '';
      filterCategory.value = '';
      filterType.value = '';
      filterStock.value = '';
      applyFilters(true);
    });
  }

  if (btnPrevPage) {
    btnPrevPage.addEventListener('click', function(){
      if (currentPage > 1) {
        currentPage--;
        renderPagination();
      }
    });
  }

  if (btnNextPage) {
    btnNextPage.addEventListener('click', function(){
      const totalPages = Math.max(1, Math.ceil(filteredRows.length / rowsPerPage));
      if (currentPage < totalPages) {
        currentPage++;
        renderPagination();
      }
    });
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-print-one');
    if (!btn) return;

    const tr = btn.closest('tr');
    if (!tr) return;

    const copiesInput = tr.querySelector('.copies-input');
    const copies = Math.max(1, parseInt(copiesInput?.value || '1', 10));

    const barcode = tr.getAttribute('data-barcode') || '';
    const barcodePath = tr.getAttribute('data-barcode-path') || '';

    if (!barcodePath && !barcode) {
      alert('Esta etiqueta no tiene barcode disponible.');
      return;
    }

    let blocks = '';
    for (let i = 0; i < copies; i++) {
      blocks += `
        <div class="label">
            ${barcodePath ? `<div class="barcode-wrap"><img src="${barcodePath}" alt="barcode"></div>` : ''}
        </div>
        `;
    }

    const w = window.open('', '_blank', 'width=900,height=700');
    w.document.open();
    w.document.write(`
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>Imprimir etiquetas</title>
<style>
  @page { margin: 6mm; }
  body{
    margin:0;
    font-family: Arial, sans-serif;
    background:#fff;
    color:#000;
  }
  .sheet{
    display:grid;
    grid-template-columns:repeat(auto-fill, minmax(50mm, 1fr));
    gap:4mm;
    padding:4mm;
  }
  .label{
    width:50mm;
    min-height:28mm;
    border:1px solid #111;
    border-radius:2mm;
    padding:3mm;
    box-sizing:border-box;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    text-align:center;
    page-break-inside:avoid;
  }
  .barcode-wrap{
    width:100%;
    display:flex;
    justify-content:center;
    align-items:center;
  }
  .barcode-wrap img{
    max-width:100%;
    max-height:14mm;
    object-fit:contain;
  }
  .barcode-text{
    margin-top:1.5mm;
    font-size:8pt;
    font-family:monospace;
    line-height:1.1;
  }
</style>
</head>
<body>
  <div class="sheet">
    ${blocks}
  </div>
</body>
</html>
    `);
    w.document.close();

    setTimeout(() => {
      w.focus();
      w.print();
    }, 350);
  });

  applyFilters(true);
})();
</script>