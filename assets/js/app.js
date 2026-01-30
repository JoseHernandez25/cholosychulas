// assets/js/app.js
console.log("app.js cargado ✅");

function orderByWhatsapp(productId) {
    const card = document.querySelector('[data-product-id="' + productId + '"]');
    if (!card) return;

    const name   = card.dataset.name;
    const price  = parseFloat(card.dataset.price || 0);
    const qtyEl  = card.querySelector('.qty-input');
    const varEl  = card.querySelector('.variant-select');

    const qty = qtyEl ? parseInt(qtyEl.value || '1', 10) : 1;

    let size  = '';
    let color = '';

    if (varEl && varEl.selectedOptions.length > 0) {
        const opt = varEl.selectedOptions[0];
        size  = opt.dataset.size || '';
        color = opt.dataset.color || '';
    }

    const subtotal = price * qty;

    let msg = 'Hola, quiero hacer un pedido:%0A%0A';
    msg += '• ' + name;

    if (size || color) {
        msg += ' (' +
            (size ? 'Talla ' + size : '') +
            (size && color ? ', ' : '') +
            (color ? 'Color ' + color : '') +
            ')';
    }

    msg += '%0ACantidad: ' + qty;
    msg += '%0ASubtotal estimado: $' + subtotal.toFixed(2);
    msg += '%0A%0AMi nombre: ';
    msg += '%0ADirección: ';
    msg += '%0AMétodo de pago: ';

    const url = 'https://wa.me/' + WHATSAPP_NUMBER + '?text=' + msg;
    window.open(url, '_blank');
}

/* =========================
   FILTRO POR CATEGORÍAS NAV
   ========================= */
document.addEventListener('DOMContentLoaded', () => {
    const links = document.querySelectorAll('.main-nav a[data-category]');
    const cards = document.querySelectorAll('.product-card');

    if (!links.length) console.log('No se encontraron links de categorías en .main-nav');
    if (!cards.length) console.log('No se encontraron .product-card');

    links.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();

            // activo
            links.forEach(a => a.classList.remove('active'));
            link.classList.add('active');

            const category = (link.dataset.category || '').toLowerCase().trim();

            cards.forEach(card => {
                const cardCat = (card.dataset.category || '').toLowerCase().trim();

                // IMPORTANTE: para grid, usa '' para mostrar, no 'block'
                card.style.display = (category === 'all' || category === cardCat) ? '' : 'none';
            });
        });
    });
});