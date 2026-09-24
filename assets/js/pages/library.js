/* ============================================================
 *  assets/js/pages/library.js — JS khusus halaman library (SPA-ready)
 * ============================================================ */
(function () {
  'use strict';

  const SAMPLES = [
    {
      id: 'sidebar',
      name: 'Sidebar Nav',
      badges: ['popular', 'layout'],
      desc: 'Sidebar navigasi dengan tombol toggle panel. Cocok untuk main menu game.',
      tags: ['nav', 'panel', 'toggle', 'menu'],
      category: 'layout',
    },
    {
      id: 'music',
      name: 'Music Player',
      badges: ['new', 'ui'],
      desc: 'Music player mini dengan progress bar, play/pause, dan next/prev.',
      tags: ['audio', 'player', 'media'],
      category: 'ui',
    },
    {
      id: 'inventory',
      name: 'Inventory Grid',
      badges: ['ui'],
      desc: 'Grid inventory dengan slot item, hover effect, dan badge rarity.',
      tags: ['grid', 'items', 'storage'],
      category: 'ui',
    },
    {
      id: 'settings',
      name: 'Settings Menu',
      badges: ['ui'],
      desc: 'Menu pengaturan dengan toggle switch, slider, dan dropdown.',
      tags: ['form', 'settings', 'config'],
      category: 'ui',
    },
    {
      id: 'shop',
      name: 'Shop Panel',
      badges: ['commerce'],
      desc: 'Panel toko dengan kartu produk, harga, dan tombol beli.',
      tags: ['shop', 'commerce', 'store'],
      category: 'commerce',
    },
    {
      id: 'dialogue',
      name: 'Dialogue Box',
      badges: ['rpg'],
      desc: 'Kotak dialog RPG dengan avatar, nama speaker, dan pilihan respons.',
      tags: ['rpg', 'dialog', 'npc', 'quest'],
      category: 'rpg',
    },
  ];

  let currentFilter = 'all';
  let currentSearch = '';

  /* ============================================================
     RENDER LIBRARY
     ============================================================ */
  function renderLibrary() {
    const grid = document.getElementById('libGrid');
    if (!grid) return;

    let filtered = SAMPLES.filter(s => {
      const matchFilter = currentFilter === 'all' || s.category === currentFilter;
      const q = currentSearch.toLowerCase().trim();
      const matchSearch = !q ||
        s.name.toLowerCase().includes(q) ||
        s.desc.toLowerCase().includes(q) ||
        s.tags.some(t => t.toLowerCase().includes(q));
      return matchFilter && matchSearch;
    });

    if (filtered.length === 0) {
      grid.innerHTML = `
        <div class="lib-empty">
          <div class="lib-empty-icon">🔍</div>
          <div class="lib-empty-title">Tidak ada template ditemukan</div>
          <div class="lib-empty-desc">Coba kata kunci lain atau ganti filter.</div>
        </div>
      `;
      return;
    }

    grid.innerHTML = filtered.map(s => `
      <div class="lib-card" onclick="loadToConverter('${s.id}')">
        <div class="lib-card-preview">
          <iframe src="${window.__samplesUrl || 'samples/'}${s.id}.html" scrolling="no" loading="lazy"></iframe>
          <div class="lib-card-overlay">
            <button class="lib-card-overlay-btn primary" onclick="event.stopPropagation(); loadToConverter('${s.id}')">
              ⚡ Open
            </button>
            <button class="lib-card-overlay-btn ghost" onclick="event.stopPropagation(); previewTemplate('${s.id}')">
              👁️ Preview
            </button>
          </div>
        </div>
        <div class="lib-card-body">
          <div class="lib-card-header">
            <div class="lib-card-name">${s.name}</div>
            <div class="lib-card-badges">
              ${s.badges.map(b => `<span class="lib-badge ${b}">${b}</span>`).join('')}
            </div>
          </div>
          <div class="lib-card-desc">${s.desc}</div>
          <div class="lib-card-meta">
            ${s.tags.map(t => `<span class="lib-chip">#${t}</span>`).join('')}
          </div>
        </div>
      </div>
    `).join('');
  }

  /* ============================================================
     ACTIONS
     ============================================================ */
  function loadToConverter(id) {
    localStorage.setItem('arrr_load_sample', id);
    // Ganti '/library' di URL dengan '/converter'
    const target = location.pathname.replace(/\/library\/?$/, '/converter');

    // Kalau SPA aktif, dispatch custom event biar spa.js handle
    // Kalau nggak, fallback ke location.href
    if (window.__spaNavigate) {
      window.__spaNavigate(target);
    } else {
      location.href = target;
    }
  }

  function previewTemplate(id) {
    window.open((window.__samplesUrl || 'samples/') + id + '.html', '_blank');
  }

  /* ============================================================
     INIT LIBRARY (idempotent)
     ============================================================ */
  function initLibrary() {
    // Guard: pastikan di halaman library
    if (!document.getElementById('libGrid')) return;

    // Skip kalau udah pernah init di halaman ini
    if (window.__libraryInitialized) {
      // Re-render aja biar state fresh
      renderLibrary();
      return;
    }
    window.__libraryInitialized = true;

    const searchInput = document.getElementById('libSearch');
    if (searchInput) {
      searchInput.addEventListener('input', (e) => {
        currentSearch = e.target.value;
        renderLibrary();
      });
    }

    const filterBar = document.getElementById('libFilters');
    if (filterBar) {
      filterBar.addEventListener('click', (e) => {
        const btn = e.target.closest('.lib-filter');
        if (!btn) return;
        currentFilter = btn.dataset.filter;
        document.querySelectorAll('.lib-filter').forEach(f => {
          f.classList.toggle('active', f === btn);
        });
        renderLibrary();
      });
    }

    renderLibrary();
  }

  /* ============================================================
     EXPOSE
     ============================================================ */
  window.loadToConverter  = loadToConverter;
  window.previewTemplate  = previewTemplate;
  window.initLibrary      = initLibrary;

  /* ============================================================
     AUTO-INIT
     ============================================================ */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initLibrary);
  } else {
    initLibrary();
  }

  // Re-init setiap SPA navigation
  window.addEventListener('spa:navigated', () => {
    window.__libraryInitialized = false;
    initLibrary();
  });

})();