<?php
$success      = $success      ?? false;
$error        = $error        ?? '';
$categories   = $categories   ?? [];
$editCategory = $editCategory ?? null;
?>

<div id="categoriesPage" class="panel panel-right form-panel product-form-panel">
  <div class="panel-head">
    <div>
      <h2 class="title-panel">Categorías</h2>
      <div class="panel-sub">Administra las categorías de tus productos.</div>
    </div>
  </div>

  <?php if ($success): ?>
    <div class="msg-ok">Categoría guardada correctamente.</div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="msg-err"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <div class="category-layout">
    <div class="category-form-card">
      <div class="section-head">
        <h3><?= $editCategory ? 'Editar categoría' : 'Nueva categoría' ?></h3>

        <?php if ($editCategory): ?>
          <a class="btn-ghost btn-sm" href="?c=category&a=index">Cancelar edición</a>
        <?php endif; ?>
      </div>

      <form class="admin-form" action="?c=category&a=save" method="POST">
        <input type="hidden" name="id" value="<?= htmlspecialchars($editCategory['id'] ?? '') ?>">

        <div class="form-grid">
          <div class="form-group form-span-2">
            <label>Nombre</label>
            <input
              type="text"
              name="name"
              required
              value="<?= htmlspecialchars($editCategory['name'] ?? '') ?>"
              placeholder="Ej. Playeras, Pantalones, Gorras"
            >
          </div>

          <div class="form-group form-span-2 checkbox-inline">
            <input
              type="checkbox"
              name="active"
              <?= (!isset($editCategory['active']) || $editCategory['active']) ? 'checked' : '' ?>
            >
            <span>Categoría activa</span>
          </div>
        </div>

        <div class="form-actions">
          <?php if ($editCategory): ?>
            <a class="btn-ghost" href="?c=category&a=index">Cancelar</a>
            <button type="submit" class="primary">Guardar cambios</button>
          <?php else: ?>
            <button type="submit" class="primary">Guardar categoría</button>
          <?php endif; ?>
        </div>
      </form>
    </div>

    <div class="category-table-card">
      <div class="section-head">
        <h3>Listado</h3>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Nombre</th>
              <th>Estado</th>
              <th>Acciones</th>
            </tr>
          </thead>

          <tbody>
          <?php if (!empty($categories)): ?>
            <?php foreach ($categories as $row): ?>
              <tr>
                <td><?= (int)$row['id'] ?></td>
                <td><strong><?= htmlspecialchars($row['name']) ?></strong></td>

                <td>
                  <span class="badge <?= !empty($row['active']) ? 'badge-active' : 'badge-inactive' ?>">
                    <?= !empty($row['active']) ? 'ACTIVA' : 'INACTIVA' ?>
                  </span>
                </td>

                <td class="actions-cell">
                  <a class="btn-link" href="?c=category&a=index&id=<?= (int)$row['id'] ?>">Editar</a>

                  <a class="btn-link"
                     href="?c=category&a=toggle&id=<?= (int)$row['id'] ?>"
                     onclick="return confirm('¿Cambiar estado de esta categoría?');">
                    <?= !empty($row['active']) ? 'Desactivar' : 'Activar' ?>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="4">No hay categorías registradas.</td>
            </tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<style>
  #categoriesPage .category-layout{
    display:grid;
    grid-template-columns:320px minmax(0, 1fr);
    gap:18px;
    align-items:start;
  }

  #categoriesPage .category-form-card,
  #categoriesPage .category-table-card{
    border:1px solid rgba(255,255,255,.08);
    border-radius:14px;
    background:rgba(255,255,255,.02);
    padding:16px;
  }

  #categoriesPage .category-form-card{
    overflow:hidden;
  }

  #categoriesPage .category-form-card .form-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:14px;
  }

  #categoriesPage .category-form-card .form-span-2{
    grid-column:auto;
  }

  #categoriesPage .category-form-card .form-group{
    min-width:0;
    width:100%;
  }

  #categoriesPage .category-form-card .form-group label{
    display:block;
    margin-bottom:8px;
  }

  #categoriesPage .category-form-card input[type="text"]{
    display:block;
    width:100% !important;
    max-width:100% !important;
    min-width:0 !important;
    box-sizing:border-box !important;
  }

  #categoriesPage .category-form-card .checkbox-inline{
    display:flex;
    align-items:center;
    gap:10px;
  }

  #categoriesPage .section-head{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
    margin-bottom:14px;
  }

  #categoriesPage .section-head h3{
    margin:0;
    font-size:1rem;
  }

  #categoriesPage .actions-cell{
    white-space:nowrap;
  }

  #categoriesPage .btn-sm{
    padding:8px 12px;
    font-size:.9rem;
  }

  @media (max-width: 980px){
    #categoriesPage .category-layout{
      grid-template-columns:1fr;
    }
  }
</style>