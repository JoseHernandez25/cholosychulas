<div class="catalog-wrap">
  <div class="grid-products is-snap-carousel">
      <?php foreach ($products as $p): ?>
          <article class="product-card"
                   data-category="<?= strtolower(trim($p['category'] ?? 'sin-categoria')) ?>"
                   data-product-id="<?= (int)$p['id'] ?>"
                   data-name="<?= htmlspecialchars($p['name'], ENT_QUOTES) ?>"
                   data-price="<?= (float)$p['price'] ?>">

              <img src="<?= $base_url . 'assets/imgs/' . htmlspecialchars($p['image_url']) ?>"
                   alt="<?= htmlspecialchars($p['name']) ?>">

              <h2><?= htmlspecialchars($p['name']) ?></h2>

              <p class="price">$<?= number_format((float)$p['price'], 2) ?></p>

              <?php if (!empty($p['variants'])): ?>
                  <label>
                      Variante:
                      <select class="variant-select">
                          <?php foreach ($p['variants'] as $v): ?>
                              <option value="<?= (int)$v['id'] ?>"
                                      data-size="<?= htmlspecialchars($v['size'], ENT_QUOTES) ?>"
                                      data-color="<?= htmlspecialchars($v['color'], ENT_QUOTES) ?>">
                                  <?= htmlspecialchars($v['size']) ?> - <?= htmlspecialchars($v['color']) ?>
                                  (<?= (int)$v['stock'] ?> disp.)
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </label>
              <?php else: ?>
                  <p style="color:#aaa; font-size:13px; margin: 8px 0;">Sin variantes disponibles</p>
              <?php endif; ?>

              <label>
                  Cantidad:
                  <input type="number" class="qty-input" min="1" value="1">
              </label>

              <button class="btn-whatsapp" type="button"
                      onclick="orderByWhatsapp(<?= (int)$p['id'] ?>)">
                  Pedir por WhatsApp
              </button>
          </article>
      <?php endforeach; ?>
  </div>
</div>