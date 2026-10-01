// ============================================
// KAIA_SV - JS General v3 (con tallas)
// ============================================

// ---------- Menú responsive ----------
const menuToggle = document.getElementById('menuToggle');
const mainNav = document.getElementById('mainNav');
if (menuToggle && mainNav) {
  menuToggle.addEventListener('click', () => mainNav.classList.toggle('open'));
}

// ---------- Toast ----------
function mostrarToast(mensaje, tipo = 'ok') {
  let toast = document.getElementById('toastGlobal');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toastGlobal';
    toast.className = 'toast';
    document.body.appendChild(toast);
  }
  const icono = tipo === 'ok' ? 'fa-circle-check' : 'fa-circle-exclamation';
  toast.className = 'toast' + (tipo === 'err' ? ' err' : '');
  toast.innerHTML = `<i class="fa-solid ${icono}"></i> <span>${mensaje}</span>`;
  void toast.offsetWidth;
  toast.classList.add('show');
  clearTimeout(toast._t);
  toast._t = setTimeout(() => toast.classList.remove('show'), 2800);
}
window.mostrarToast = mostrarToast;

// ---------- Ampliar imagen del producto ----------
(() => {
  const trigger = document.querySelector('[data-image-modal-open]');
  const modal = document.getElementById('productoImagenModal');
  const closeButton = modal?.querySelector('.producto-imagen-cerrar');
  if (!trigger || !modal || !closeButton) return;

  function cerrarModalImagen() {
    modal.hidden = true;
    document.body.classList.remove('modal-open');
    trigger.focus();
  }

  trigger.addEventListener('click', () => {
    modal.hidden = false;
    document.body.classList.add('modal-open');
    closeButton.focus();
  });

  closeButton.addEventListener('click', cerrarModalImagen);
  modal.addEventListener('click', event => {
    if (event.target === modal) cerrarModalImagen();
  });
  modal.addEventListener('keydown', event => {
    if (event.key === 'Escape') cerrarModalImagen();
    if (event.key === 'Tab') {
      event.preventDefault();
      closeButton.focus();
    }
  });
})();

// ---------- Agregar al carrito (con talla) ----------
function agregarAlCarrito(productoId, cantidad, talla) {
  fetch('/kaia_sv/api/agregar_carrito.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `producto_id=${productoId}&cantidad=${cantidad}&talla=${encodeURIComponent(talla)}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      mostrarToast(`¡Agregado! Talla ${talla}`);
      const c = document.getElementById('cartCount');
      if (c) c.textContent = data.total_items;
      if (window.gsap) {
        gsap.fromTo('#cartCount',
          { scale: 0.5 },
          { scale: 1.4, duration: 0.3, yoyo: true, repeat: 1, ease: 'power2.out' });
      }
    } else {
      mostrarToast(data.msg || 'Error al agregar', 'err');
    }
  })
  .catch(() => mostrarToast('Error de conexión', 'err'));
}

// ---------- Modal de selección de talla ----------
function abrirModalTalla(productoId, nombreProducto) {
  fetch(`/kaia_sv/api/obtener_tallas.php?producto_id=${productoId}`)
    .then(r => r.json())
    .then(data => {
      if (!data.ok || !data.tallas.length) {
        agregarAlCarrito(productoId, 1, 'Única');
        return;
      }

      const conStock = data.tallas.filter(t => t.stock > 0);
      if (!conStock.length) {
        mostrarToast('Producto agotado', 'err');
        return;
      }
      if (conStock.length === 1) {
        agregarAlCarrito(productoId, 1, conStock[0].talla);
        return;
      }

      const modal = document.createElement('div');
      modal.className = 'modal-talla-backdrop';
      modal.innerHTML = `
        <div class="modal-talla">
          <button class="modal-close" aria-label="Cerrar">&times;</button>
          <h3 class="modal-title">Selecciona tu talla</h3>
          <p class="modal-sub">${nombreProducto}</p>
          <div class="talla-grid">
            ${data.tallas.map(t => `
              <button class="talla-opt ${t.stock === 0 ? 'agotada' : ''}"
                      data-talla="${t.talla}" data-stock="${t.stock}"
                      ${t.stock === 0 ? 'disabled' : ''}>
                <span class="talla-nombre">${t.talla}</span>
                <span class="talla-stock">${t.stock > 0 ? t.stock + ' disp.' : 'agotada'}</span>
              </button>
            `).join('')}
          </div>
          <div class="modal-qty">
            <label>Cantidad:</label>
            <input type="number" id="modalQty" value="1" min="1" max="1">
          </div>
          <button class="btn btn-primary modal-confirm" disabled>
            Agregar al carrito
          </button>
        </div>
      `;
      document.body.appendChild(modal);

      if (window.gsap) {
        gsap.fromTo(modal.querySelector('.modal-talla'),
          { y: 40, opacity: 0, scale: 0.95 },
          { y: 0, opacity: 1, scale: 1, duration: 0.35, ease: 'back.out(1.4)' });
        gsap.fromTo(modal, { opacity: 0 }, { opacity: 1, duration: 0.25 });
      }

      let tallaSel = null;
      const btnConfirm = modal.querySelector('.modal-confirm');
      const qtyInput = modal.querySelector('#modalQty');

      modal.querySelectorAll('.talla-opt').forEach(btn => {
        btn.addEventListener('click', () => {
          if (btn.disabled) return;
          modal.querySelectorAll('.talla-opt').forEach(b => b.classList.remove('selected'));
          btn.classList.add('selected');
          tallaSel = btn.dataset.talla;
          const max = parseInt(btn.dataset.stock);
          qtyInput.max = max;
          qtyInput.value = Math.min(parseInt(qtyInput.value) || 1, max);
          btnConfirm.disabled = false;
        });
      });

      function cerrar() {
        if (window.gsap) {
          gsap.to(modal.querySelector('.modal-talla'), {
            y: 40, opacity: 0, scale: 0.95, duration: 0.25,
            onComplete: () => modal.remove()
          });
        } else {
          modal.remove();
        }
      }

      btnConfirm.addEventListener('click', () => {
        if (!tallaSel) return;
        agregarAlCarrito(productoId, parseInt(qtyInput.value) || 1, tallaSel);
        cerrar();
      });

      modal.querySelector('.modal-close').addEventListener('click', cerrar);
      modal.addEventListener('click', e => { if (e.target === modal) cerrar(); });
      document.addEventListener('keydown', function esc(e) {
        if (e.key === 'Escape') { cerrar(); document.removeEventListener('keydown', esc); }
      });
    })
    .catch(() => mostrarToast('Error al cargar tallas', 'err'));
}
window.abrirModalTalla = abrirModalTalla;

// ---------- Detectar click en "Agregar al carrito" ----------
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-add-cart]');
  if (!btn) return;
  e.preventDefault();
  const id = btn.dataset.addCart;
  const nombre = btn.dataset.nombre || 'Producto';

  // Si el botón ya trae talla fija (desde producto.php)
  if (btn.dataset.talla) {
    agregarAlCarrito(id, parseInt(btn.dataset.cantidad) || 1, btn.dataset.talla);
  } else {
    abrirModalTalla(id, nombre);
  }
});

// ---------- Wishlist ----------
document.addEventListener('click', (e) => {
  const btn = e.target.closest('[data-wish]');
  if (!btn) return;
  e.preventDefault();

  if (btn.dataset.guest === '1') {
    mostrarToast('Inicia sesión para añadir a favoritos', 'err');
    setTimeout(() => window.location.href = '/kaia_sv/login.php?msg=login_required', 1500);
    return;
  }

  const id = btn.dataset.wish;
  fetch('/kaia_sv/api/toggle_wishlist.php', {
    method: 'POST',
    credentials: 'same-origin',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `producto_id=${id}`
  })
  .then(r => r.json())
  .then(data => {
    if (data.ok) {
      btn.classList.toggle('active', data.en_wishlist);
      const icon = btn.querySelector('i');
      if (icon) icon.className = data.en_wishlist ? 'fa-solid fa-heart' : 'fa-regular fa-heart';
      const cont = document.getElementById('wishCount');
      if (cont) cont.textContent = data.total;
      mostrarToast(data.en_wishlist ? 'Guardado en favoritos' : 'Eliminado de favoritos');
    } else {
      mostrarToast(data.msg || 'Error', 'err');
    }
  })
  .catch(() => mostrarToast('Error de conexión', 'err'));
});

// ---------- Actualizar cantidad en carrito ----------
document.querySelectorAll('.qty-input').forEach(input => {
  input.addEventListener('change', function () {
    const clave = this.dataset.clave;
    const cantidad = parseInt(this.value) || 1;
    fetch('/kaia_sv/api/actualizar_carrito.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `clave=${encodeURIComponent(clave)}&cantidad=${cantidad}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) {
        location.reload();
      } else {
        mostrarToast(data.msg || 'No se pudo actualizar la cantidad', 'err');
        setTimeout(() => location.reload(), 900);
      }
    })
    .catch(() => mostrarToast('Error al actualizar el carrito', 'err'));
  });
});

// ---------- Confirmar eliminación del carrito ----------
(() => {
  const modal = document.getElementById('cartDeleteModal');
  const mensaje = document.getElementById('cartDeleteMessage');
  const confirmar = document.getElementById('confirmCartDelete');
  if (!modal || !mensaje || !confirmar) return;

  let botonActivo = null;
  const cancelar = modal.querySelector('[data-close-cart-modal]');

  function cerrarModal() {
    modal.hidden = true;
    confirmar.disabled = false;
    if (botonActivo) botonActivo.focus();
    botonActivo = null;
  }

  document.querySelectorAll('[data-remove-cart]').forEach(btn => {
    btn.addEventListener('click', () => {
      botonActivo = btn;
      mensaje.textContent = `¿Deseas eliminar "${btn.dataset.productName}" del carrito?`;
      modal.hidden = false;
      cancelar.focus();
    });
  });

  modal.querySelectorAll('[data-close-cart-modal]').forEach(btn => {
    btn.addEventListener('click', cerrarModal);
  });

  modal.addEventListener('click', event => {
    if (event.target === modal) cerrarModal();
  });

  document.addEventListener('keydown', event => {
    if (modal.hidden) return;
    if (event.key === 'Escape') cerrarModal();
    if (event.key === 'Tab') {
      const controles = [...modal.querySelectorAll('button:not(:disabled)')];
      const primero = controles[0];
      const ultimo = controles[controles.length - 1];
      if (event.shiftKey && document.activeElement === primero) {
        event.preventDefault();
        ultimo.focus();
      } else if (!event.shiftKey && document.activeElement === ultimo) {
        event.preventDefault();
        primero.focus();
      }
    }
  });

  confirmar.addEventListener('click', () => {
    if (!botonActivo) return;
    confirmar.disabled = true;
    fetch('/kaia_sv/api/eliminar_carrito.php', {
      method: 'POST',
      credentials: 'same-origin',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `clave=${encodeURIComponent(botonActivo.dataset.removeCart)}`
    })
    .then(r => r.json())
    .then(data => {
      if (data.ok) location.reload();
      else {
        cerrarModal();
        mostrarToast(data.msg || 'No se pudo eliminar el producto', 'err');
      }
    })
    .catch(() => {
      cerrarModal();
      mostrarToast('Error al eliminar el producto', 'err');
    });
  });
})();