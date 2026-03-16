// assets/js/pos.js
(function () {
  // Requiere que la vista defina window.POS_CFG antes de cargar este archivo
  if (!window.POS_CFG) return;

  const URL_BUSCAR = window.POS_CFG.URL_BUSCAR;
  const URL_COBRAR = window.POS_CFG.URL_COBRAR;
  const CAJA_ABIERTA = !!window.POS_CFG.CAJA_ABIERTA;

  // carrito item:
  // {
  //   id,
  //   variant_id,
  //   nombre,
  //   nombre_base,
  //   precio,
  //   qty,
  //   size,
  //   color,
  //   barcode,
  //   has_variants
  // }
  let carrito = [];
  let selectedKey = null;
  let lastTicketHtml = '';

  const scanInput   = document.getElementById('scanInput');
  const scanStatus  = document.getElementById('scanStatus');
  const tbody       = document.getElementById('tbodyCarrito');
  const totalTxt    = document.getElementById('totalTxt');
  const itemsTxt    = document.getElementById('itemsTxt');
  const pzasTxt     = document.getElementById('pzasTxt');
  const btnClear    = document.getElementById('btnClear');
  const btnFocus    = document.getElementById('btnFocus');
  const btnCobrar   = document.getElementById('btnCobrar');
  const btnImprimir = document.getElementById('btnImprimir');
  const pillConn    = document.getElementById('pillConn');
  const saleInfo    = document.getElementById('saleInfo');

  // Si la vista no es la de caja, no hacemos nada
  if (!scanInput) return;

  function focusScan() {
    if (!CAJA_ABIERTA) return;
    try { scanInput.focus(); } catch (e) {}
  }

  function setStatus(msg, type = '') {
    if (!scanStatus) return;
    scanStatus.className = 'pos-status ' + (type || '');
    scanStatus.textContent = msg;
  }

  function money(n) {
    return '$' + Number(n || 0).toFixed(2);
  }

  function escapeHtml(s) {
    return String(s ?? '').replace(/[&<>"']/g, m => ({
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    }[m]));
  }

  function beep(freq, dur, delay = 0) {
    try {
      const ctx = new (window.AudioContext || window.webkitAudioContext)();
      const o = ctx.createOscillator();
      const g = ctx.createGain();
      o.type = 'sine';
      o.frequency.value = freq;
      o.connect(g);
      g.connect(ctx.destination);

      const t0 = ctx.currentTime + delay;
      g.gain.setValueAtTime(0.0001, t0);
      g.gain.exponentialRampToValueAtTime(0.12, t0 + 0.01);
      o.start(t0);
      g.gain.exponentialRampToValueAtTime(0.0001, t0 + dur);
      o.stop(t0 + dur + 0.02);
    } catch (e) {}
  }

  function ok()  { beep(820, 0.05); }
  function bad() { beep(220, 0.08); beep(180, 0.08, 0.09); }

  function getItemKey(item) {
    return item.variant_id ? ('v-' + item.variant_id) : ('p-' + item.id);
  }

  function getDisplayMeta(item) {
    const parts = [];
    if (item.size) parts.push(item.size);
    if (item.color) parts.push(item.color);
    return parts.join(' / ');
  }

  function render() {
    if (!tbody) return;

    if (!CAJA_ABIERTA) {
      tbody.innerHTML = `<tr><td colspan="5" class="muted">Abre una caja para comenzar…</td></tr>`;
      if (totalTxt) totalTxt.textContent = money(0);
      if (itemsTxt) itemsTxt.textContent = '0';
      if (pzasTxt)  pzasTxt.textContent  = '0';
      return;
    }

    if (!carrito.length) {
      tbody.innerHTML = `<tr><td colspan="5" class="muted">Escanea para comenzar…</td></tr>`;
      if (totalTxt) totalTxt.textContent = money(0);
      if (itemsTxt) itemsTxt.textContent = '0';
      if (pzasTxt)  pzasTxt.textContent  = '0';
      return;
    }

    let total = 0;
    let pzas = 0;

    tbody.innerHTML = carrito.map(it => {
      const sub = Number(it.precio) * Number(it.qty);
      total += sub;
      pzas += Number(it.qty);

      const rowKey = getItemKey(it);
      const sel = (rowKey === selectedKey) ? 'pos-row sel' : 'pos-row';
      const meta = getDisplayMeta(it);

      return `
        <tr class="${sel}" data-key="${escapeHtml(rowKey)}">
          <td>
            <b>${escapeHtml(it.nombre_base || it.nombre)}</b>
            ${meta ? `<div class="muted">${escapeHtml(meta)}</div>` : ''}
            <div class="muted">
              ${it.variant_id ? `Variante: ${it.variant_id}` : `ID: ${it.id}`}
            </div>
          </td>
          <td class="r">${money(it.precio)}</td>
          <td class="r">${it.qty}</td>
          <td class="r"><b>${money(sub)}</b></td>
          <td class="r">
            <button class="pos-btn danger" data-del="${escapeHtml(rowKey)}" type="button">Borrar</button>
          </td>
        </tr>
      `;
    }).join('');

    if (totalTxt) totalTxt.textContent = money(total);
    if (itemsTxt) itemsTxt.textContent = String(carrito.length);
    if (pzasTxt)  pzasTxt.textContent  = String(pzas);

    tbody.querySelectorAll('tr[data-key]').forEach(tr => {
      tr.addEventListener('click', () => {
        selectedKey = tr.dataset.key;
        render();
        focusScan();
      });
    });

    tbody.querySelectorAll('button[data-del]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.stopPropagation();
        const key = btn.dataset.del;

        carrito = carrito.filter(x => getItemKey(x) !== key);
        selectedKey = carrito.length ? getItemKey(carrito[carrito.length - 1]) : null;

        render();
        focusScan();
      });
    });
  }

  async function buscarYAgregar(barcode) {
    if (!CAJA_ABIERTA) {
      setStatus('Caja cerrada. No se puede escanear.', 'warn');
      bad();
      return;
    }

    try {
      setStatus('Buscando: ' + barcode, '');
      if (pillConn) pillConn.textContent = 'Buscando…';

      const sep = URL_BUSCAR.includes('?') ? '&' : '?';
      const res = await fetch(URL_BUSCAR + sep + 'barcode=' + encodeURIComponent(barcode), {
        cache: 'no-store'
      });

      const data = await res.json();

      if (pillConn) pillConn.textContent = 'Listo';

      if (!data.ok) {
        setStatus(data.msg || 'No encontrado', 'bad');
        bad();
        return;
      }

      const p = data.producto;

      const newItem = {
        id: Number(p.id),
        variant_id: p.variant_id ? Number(p.variant_id) : null,
        nombre: p.nombre,
        nombre_base: p.nombre_base || p.nombre,
        precio: Number(p.precio),
        qty: 1,
        size: p.size || null,
        color: p.color || null,
        barcode: p.barcode || null,
        has_variants: Number(p.has_variants || 0)
      };

      const key = getItemKey(newItem);
      const item = carrito.find(x => getItemKey(x) === key);

      if (item) {
        item.qty += 1;
      } else {
        carrito.push(newItem);
      }

      selectedKey = key;
      render();
      setStatus('Agregado: ' + p.nombre, 'ok');
      ok();

    } catch (err) {
      console.error(err);
      if (pillConn) pillConn.textContent = 'Error';
      setStatus('Error JS/Servidor', 'bad');
      bad();
    } finally {
      focusScan();
    }
  }

  async function cobrar() {
    if (!CAJA_ABIERTA) {
      setStatus('No hay una caja abierta', 'bad');
      bad();
      return;
    }

    if (!carrito.length) {
      setStatus('Carrito vacío', 'warn');
      return;
    }

    try {
      if (pillConn) pillConn.textContent = 'Guardando…';
      setStatus('Registrando venta…', '');

      const payload = {
        items: carrito.map(x => ({
          id: x.id,
          variant_id: x.variant_id,
          nombre: x.nombre,
          precio: x.precio,
          qty: x.qty,
          size: x.size,
          color: x.color,
          barcode: x.barcode
        }))
      };

      const res = await fetch(URL_COBRAR, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });

      const data = await res.json();

      if (pillConn) pillConn.textContent = 'Listo';

      if (!data.ok) {
        setStatus(data.msg || 'No se pudo cobrar', 'bad');
        bad();
        return;
      }

      if (saleInfo) saleInfo.textContent = 'Última venta: #' + data.id_venta;
      lastTicketHtml = data.ticket_html || '';

      carrito = [];
      selectedKey = null;
      render();
      setStatus('Venta registrada', 'ok');
      ok();

      // recargar para refrescar resumen de caja
      setTimeout(() => {
        window.location.reload();
      }, 700);

    } catch (err) {
      console.error(err);
      if (pillConn) pillConn.textContent = 'Error';
      setStatus('Error al cobrar', 'bad');
      bad();
    } finally {
      focusScan();
    }
  }

  function printTicket() {
    if (!CAJA_ABIERTA) {
      setStatus('No hay una caja abierta', 'warn');
      return;
    }

    if (!lastTicketHtml) {
      setStatus('No hay ticket (cobra primero)', 'warn');
      return;
    }

    const w = window.open('', '_blank', 'width=420,height=700');
    w.document.open();
    w.document.write(lastTicketHtml);
    w.document.close();
    w.focus();
    w.print();
  }

  function clearCart() {
    if (!CAJA_ABIERTA) {
      setStatus('Caja cerrada', 'warn');
      return;
    }

    carrito = [];
    selectedKey = null;
    render();
    setStatus('Carrito vacío', 'warn');
    focusScan();
  }

  // ===== Eventos =====
  if (CAJA_ABIERTA) {
    focusScan();
    document.addEventListener('click', focusScan);
  }

  if (btnFocus) {
    btnFocus.addEventListener('click', () => {
      if (!CAJA_ABIERTA) {
        setStatus('Caja cerrada. Abre una caja primero.', 'warn');
        return;
      }
      focusScan();
    });
  }

  if (btnClear) btnClear.addEventListener('click', clearCart);

  scanInput.addEventListener('keydown', (e) => {
    if (!CAJA_ABIERTA) return;
    if (e.key !== 'Enter') return;

    const code = scanInput.value.trim();
    if (!code) return;

    scanInput.value = '';
    buscarYAgregar(code);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && CAJA_ABIERTA) focusScan();

    if (e.key === 'F2') {
      e.preventDefault();
      cobrar();
    }
  });

  if (btnCobrar)   btnCobrar.addEventListener('click', cobrar);
  if (btnImprimir) btnImprimir.addEventListener('click', printTicket);

  render();

  if (CAJA_ABIERTA) {
    setStatus('Esperando escaneo…', '');
  } else {
    setStatus('Caja cerrada. Abre una caja para comenzar.', 'warn');
    if (pillConn) pillConn.textContent = 'Caja cerrada';
  }
})();