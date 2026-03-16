<?php
$success  = $success  ?? false;
$error    = $error    ?? '';
$products = $products ?? [];

// categorías únicas para filtro
$categoryOptions = [];
foreach ($products as $p) {
    $cat = trim((string)($p['category'] ?? ''));
    if ($cat !== '') {
        $categoryOptions[$cat] = $cat;
    }
}
ksort($categoryOptions);
?>

<div class="panel panel-right form-panel product-form-panel">
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

  <div class="filters-card">
    <div class="filters-head">
      <h3>Filtros</h3>
      <button type="button" class="btn-ghost btn-sm" id="btnClearFilters">Limpiar</button>
    </div>

    <div class="filters-grid">
      <div class="form-group">
        <label for="filterName">Buscar por nombre</label>
        <input type="text" id="filterName" placeholder="Ej. hoodie, gorra, pantalón">
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
          <option value="simple">Simples</option>
          <option value="variants">Con variantes</option>
        </select>
      </div>

      <div class="form-group">
        <label for="filterStatus">Estado</label>
        <select id="filterStatus">
          <option value="">Todos</option>
          <option value="1">Activos</option>
          <option value="0">Inactivos</option>
        </select>
      </div>
    </div>
  </div>

  <div class="table-wrap">
    <table id="productsTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Nombre</th>
          <th>Categoría</th>
          <th>Tipo</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Estado</th>
          <th>Acciones</th>
        </tr>
      </thead>

      <tbody>
      <?php if (!empty($products)): ?>
        <?php foreach ($products as $row): ?>
          <?php
            $hasVariants = !empty($row['has_variants']);
            $typeValue   = $hasVariants ? 'variants' : 'simple';
            $statusValue = !empty($row['active']) ? '1' : '0';
            $categoryVal = trim((string)($row['category'] ?? ''));
            $nameVal     = trim((string)($row['name'] ?? ''));
          ?>
          <tr
            data-name="<?= htmlspecialchars(mb_strtolower($nameVal)) ?>"
            data-category="<?= htmlspecialchars(mb_strtolower($categoryVal)) ?>"
            data-type="<?= $typeValue ?>"
            data-status="<?= $statusValue ?>"
          >
            <td><?= (int)$row['id'] ?></td>

            <td>
              <div class="prod-name"><?= htmlspecialchars($row['name']) ?></div>
            </td>

            <td><?= htmlspecialchars($row['category'] ?? '') ?></td>

            <td>
              <?php if ($hasVariants): ?>
                <div class="type-actions">
                  <button
                    type="button"
                    class="badge badge-variant btn-variants"
                    data-product-id="<?= (int)$row['id'] ?>"
                    data-product-name="<?= htmlspecialchars($row['name']) ?>"
                  >
                    VARIANTES
                  </button>

                  <button
                    type="button"
                    class="btn-link btn-variants btn-variants-link"
                    data-product-id="<?= (int)$row['id'] ?>"
                    data-product-name="<?= htmlspecialchars($row['name']) ?>"
                  >
                    Ver
                  </button>
                </div>
              <?php else: ?>
                <span class="badge badge-simple">SIMPLE</span>
              <?php endif; ?>
            </td>

            <td>$<?= number_format((float)($row['price'] ?? 0), 2) ?></td>

            <td><?= (int)($row['stock'] ?? 0) ?></td>

            <td>
              <span class="badge <?= !empty($row['active']) ? 'badge-active' : 'badge-inactive' ?>">
                <?= !empty($row['active']) ? 'ACTIVO' : 'INACTIVO' ?>
              </span>
            </td>

            <td class="actions-cell">
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
        <tr id="emptyRowStatic">
          <td colspan="8">No hay productos registrados.</td>
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
    No se encontraron productos con esos filtros.
  </div>
</div>

<!-- MODAL VARIANTES -->
<div id="variantsModal" class="modal" aria-hidden="true">
  <div class="modal-backdrop" data-close-variants="1"></div>

  <div class="modal-card modal-card-lg" role="dialog" aria-modal="true" aria-labelledby="variantsTitle">
    <div class="modal-head">
      <div>
        <div id="variantsTitle" class="modal-title">Variantes</div>
        <div id="variantsSub" class="modal-sub"></div>
      </div>

      <button type="button" class="modal-x" data-close-variants="1">✕</button>
    </div>

    <div class="modal-body">
      <div id="variantsLoading" class="muted">Cargando variantes...</div>

      <div class="table-responsive variants-scroll-wrap" id="variantsTableWrap" style="display:none;">
        <table class="variants-modal-table">
          <thead>
            <tr>
              <th>Talla</th>
              <th>Color</th>
              <th>Precio</th>
              <th>Stock</th>
              <th>Barcode</th>
              <th>Estado</th>
            </tr>
          </thead>
          <tbody id="variantsModalBody"></tbody>
        </table>
      </div>
    </div>

    <div class="modal-actions">
      <button type="button" class="btn-ghost" data-close-variants="1">Cerrar</button>
    </div>
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

  .btn-sm{
    padding:8px 12px;
    font-size:.9rem;
  }

  .prod-name{
    font-weight:600;
  }

  .actions-cell{
    white-space:nowrap;
  }

  .type-actions{
    display:flex;
    flex-direction:column;
    align-items:flex-start;
    gap:4px;
  }

  .badge-simple{
    background:rgba(13, 110, 253, .15);
    color:#7db1ff;
    border:1px solid rgba(13, 110, 253, .25);
  }

  .badge-variant{
    background:rgba(255, 193, 7, .12);
    color:#ffd666;
    border:1px solid rgba(255, 193, 7, .2);
    cursor:pointer;
  }

  .btn-variants-link{
    padding:0;
    font-size:.88rem;
  }

  .empty-filters{
    margin-top:14px;
    padding:14px 16px;
    border-radius:12px;
    background:rgba(255,255,255,.03);
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

  .modal-card-lg{
    width:min(980px, calc(100vw - 32px));
  }

  .variants-scroll-wrap{
    width:100%;
    overflow-x:auto;
    overflow-y:hidden;
    padding-bottom:6px;
  }

  .variants-scroll-wrap::-webkit-scrollbar{
    height:10px;
  }

  .variants-scroll-wrap::-webkit-scrollbar-track{
    background:rgba(255,255,255,.06);
    border-radius:999px;
  }

  .variants-scroll-wrap::-webkit-scrollbar-thumb{
    background:rgba(255,255,255,.22);
    border-radius:999px;
  }

  .variants-scroll-wrap::-webkit-scrollbar-thumb:hover{
    background:rgba(255,255,255,.32);
  }

  .variants-modal-table{
    width:100%;
    min-width:820px;
    border-collapse:collapse;
  }

  .variants-modal-table th,
  .variants-modal-table td{
    padding:10px 8px;
    border-bottom:1px solid rgba(255,255,255,.08);
    vertical-align:middle;
  }

  .variants-modal-table th{
    text-align:left;
    font-size:.9rem;
    opacity:.85;
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
  const filterStatus = document.getElementById('filterStatus');
  const btnClear = document.getElementById('btnClearFilters');
  const tbody = document.querySelector('#productsTable tbody');
  const noResults = document.getElementById('noResults');

  const paginationWrap = document.getElementById('paginationWrap');
  const btnPrevPage = document.getElementById('btnPrevPage');
  const btnNextPage = document.getElementById('btnNextPage');
  const paginationInfo = document.getElementById('paginationInfo');

  const rowsPerPage = 10;
  let currentPage = 1;
  let filteredRows = [];

  if (!tbody) return;

  function normalize(value){
    return (value || '').toString().trim().toLowerCase();
  }

  function getVisibleRowsByFilter() {
    const nameVal = normalize(filterName.value);
    const categoryVal = normalize(filterCategory.value);
    const typeVal = normalize(filterType.value);
    const statusVal = normalize(filterStatus.value);

    const rows = Array.from(tbody.querySelectorAll('tr')).filter(row => row.id !== 'emptyRowStatic');

    return rows.filter(row => {
      const rowName = normalize(row.getAttribute('data-name'));
      const rowCategory = normalize(row.getAttribute('data-category'));
      const rowType = normalize(row.getAttribute('data-type'));
      const rowStatus = normalize(row.getAttribute('data-status'));

      if (nameVal && !rowName.includes(nameVal)) return false;
      if (categoryVal && rowCategory !== categoryVal) return false;
      if (typeVal && rowType !== typeVal) return false;
      if (statusVal && rowStatus !== statusVal) return false;

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

  [filterName, filterCategory, filterType, filterStatus].forEach(el => {
    if (!el) return;
    el.addEventListener('input', () => applyFilters(true));
    el.addEventListener('change', () => applyFilters(true));
  });

  if (btnClear) {
    btnClear.addEventListener('click', function(){
      filterName.value = '';
      filterCategory.value = '';
      filterType.value = '';
      filterStatus.value = '';
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

  applyFilters(true);
})();
</script>

<script>
(function(){
  const modal = document.getElementById('variantsModal');
  const title = document.getElementById('variantsTitle');
  const sub = document.getElementById('variantsSub');
  const loading = document.getElementById('variantsLoading');
  const tableWrap = document.getElementById('variantsTableWrap');
  const tbody = document.getElementById('variantsModalBody');

  function closeModal(){
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    sub.textContent = '';
    loading.style.display = 'block';
    loading.textContent = 'Cargando variantes...';
    tableWrap.style.display = 'none';
    tbody.innerHTML = '';
  }

  function badgeEstado(active){
    return active
      ? '<span class="badge badge-active">ACTIVA</span>'
      : '<span class="badge badge-inactive">INACTIVA</span>';
  }

  async function openVariants(productId, productName){
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    title.textContent = 'Variantes';
    sub.textContent = productName || '';
    loading.style.display = 'block';
    loading.textContent = 'Cargando variantes...';
    tableWrap.style.display = 'none';
    tbody.innerHTML = '';

    try {
      const res = await fetch(`?c=product&a=variants&id=${encodeURIComponent(productId)}`, {
        cache: 'no-store'
      });
      const data = await res.json();

      if (!data.ok) {
        loading.textContent = data.msg || 'No se pudieron cargar las variantes';
        return;
      }

      const variants = Array.isArray(data.variants) ? data.variants : [];

      if (!variants.length) {
        loading.textContent = 'Este producto no tiene variantes registradas.';
        return;
      }

      tbody.innerHTML = variants.map(v => `
        <tr>
          <td>${v.size ? escapeHtml(v.size) : '—'}</td>
          <td>${v.color ? escapeHtml(v.color) : '—'}</td>
          <td>$${Number(v.price || 0).toFixed(2)}</td>
          <td>${Number(v.stock || 0)}</td>
          <td>${v.barcode ? escapeHtml(v.barcode) : '—'}</td>
          <td>${badgeEstado(Number(v.active || 0) === 1)}</td>
        </tr>
      `).join('');

      loading.style.display = 'none';
      tableWrap.style.display = 'block';

    } catch (err) {
      loading.textContent = 'Error al cargar variantes';
    }
  }

  function escapeHtml(s){
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[m]));
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.btn-variants');
    if (btn) {
      openVariants(btn.getAttribute('data-product-id'), btn.getAttribute('data-product-name'));
      return;
    }

    if (e.target && e.target.getAttribute('data-close-variants') === '1') {
      closeModal();
      return;
    }
  });

  document.addEventListener('keydown', function(e){
    if (e.key === 'Escape' && modal.classList.contains('is-open')) {
      closeModal();
    }
  });
})();
</script>