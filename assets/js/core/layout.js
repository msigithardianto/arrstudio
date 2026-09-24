/* ============================================================
   assets/js/core/layout.js — UI global: plugin modal, toast, navbar
   ============================================================ */
/* ============================================================
   PLUGIN MODAL — global
   ============================================================ */
function openPluginModal() {
  const modal = document.getElementById('pluginModal');
  if (modal) modal.classList.add('show');
  document.body.style.overflow = 'hidden';
}

function closePluginModal() {
  const modal = document.getElementById('pluginModal');
  if (modal) modal.classList.remove('show');
  document.body.style.overflow = '';
}

function copyPluginPath(elementId) {
  const el = document.getElementById(elementId);
  if (!el) return;
  const text = el.textContent;
  const btn = el.nextElementSibling;
  if (navigator.clipboard) {
    navigator.clipboard.writeText(text).then(() => {
      if (btn) {
        const original = btn.textContent;
        btn.textContent = '✓';
        btn.classList.add('copied');
        setTimeout(() => {
          btn.textContent = original;
          btn.classList.remove('copied');
        }, 1200);
      }
    });
  }
}

/* ============================================================
   TOAST — global
   ============================================================ */
function showToast(message, type = 'success') {
  const toast = document.getElementById('toast');
  const icon = document.getElementById('toastIcon');
  const text = document.getElementById('toastText');
  if (!toast) return;

  toast.className = 'toast ' + type;
  icon.textContent = type === 'error' ? '✕' : (type === 'warning' ? '⚠' : '✓');
  text.textContent = message;
  toast.classList.add('show');

  clearTimeout(window._toastTimer);
  window._toastTimer = setTimeout(() => {
    toast.classList.remove('show');
  }, 2200);
}

/* ============================================================
   NAVBAR — interactive
   ============================================================ */
(function initNavbar() {
  function setup() {
    const header      = document.getElementById('appHeader');
    const toggle      = document.getElementById('navToggle');
    const nav         = document.getElementById('topNav');
    const overlay     = document.getElementById('navOverlay');
    const profileChip = document.getElementById('profileChip');

    if (!header) return;
    if (header.dataset.navbarInit === '1') return;
    header.dataset.navbarInit = '1';

    if (toggle && nav) {
      toggle.addEventListener('click', (e) => {
        e.stopPropagation();
        toggle.classList.toggle('open');
        nav.classList.toggle('open');
        overlay?.classList.toggle('show');
      });
    }

    overlay?.addEventListener('click', () => {
      toggle?.classList.remove('open');
      nav?.classList.remove('open');
      overlay?.classList.remove('show');
    });

    if (profileChip) {
      profileChip.addEventListener('click', (e) => {
        e.stopPropagation();
        profileChip.classList.toggle('open');
      });
    }
  }

  // Auto-hide navbar saat scroll ke bawah — listener dipasang SEKALI,
  // header dicari ulang tiap event (header bisa diganti oleh SPA)
  let lastScroll = 0;
  let ticking = false;
  window.addEventListener('scroll', () => {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(() => {
      const header  = document.getElementById('appHeader');
      const current = window.scrollY;
      if (header) header.classList.toggle('hidden', current > lastScroll && current > 100);
      lastScroll = current;
      ticking = false;
    });
  }, { passive: true });

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', setup);
  } else {
    setup();
  }
  window.addEventListener('spa:navigated', setup);

  document.addEventListener('click', (e) => {
    const profileChip = document.getElementById('profileChip');
    const nav         = document.getElementById('topNav');
    const toggle      = document.getElementById('navToggle');
    const overlay     = document.getElementById('navOverlay');

    if (profileChip && !profileChip.contains(e.target)) {
      profileChip.classList.remove('open');
    }
    if (nav && !nav.contains(e.target) && !toggle?.contains(e.target)) {
      toggle?.classList.remove('open');
      nav.classList.remove('open');
      overlay?.classList.remove('show');
    }
  });

  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    document.getElementById('profileChip')?.classList.remove('open');
    document.getElementById('navToggle')?.classList.remove('open');
    document.getElementById('topNav')?.classList.remove('open');
    document.getElementById('navOverlay')?.classList.remove('show');
    if (typeof closePluginModal === 'function') closePluginModal();
    if (typeof window.__closeGuestGate === 'function') window.__closeGuestGate();
  });
})();

/* ============================================================
   MODAL BACKDROP — click outside to close
   ============================================================ */
document.addEventListener('click', (e) => {
  const modal = document.getElementById('pluginModal');
  if (modal && e.target === modal) closePluginModal();
});
