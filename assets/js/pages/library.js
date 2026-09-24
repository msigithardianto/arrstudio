/* ============================================================
 *  assets/js/pages/library.js — JS khusus halaman library (SPA-ready)
 * ============================================================ */
(function () {
  'use strict';

  // logic: aksi server yang otomatis dibuatkan di tab "Game Logic"
  const SAMPLES = [
    { id: 'sidebar',   name: 'Sidebar Nav',     badges: ['popular', 'layout'], category: 'layout',
      desc: 'Sidebar navigasi dengan tombol toggle panel. Cocok untuk main menu game.',
      tags: ['nav', 'panel', 'toggle', 'menu'] },
    { id: 'music',     name: 'Music Player',    badges: ['ui'], category: 'ui',
      desc: 'Music player mini dengan progress bar, play/pause, dan next/prev.',
      tags: ['audio', 'player', 'media'] },
    { id: 'inventory', name: 'Inventory Grid',  badges: ['ui'], category: 'ui',
      desc: 'Grid inventory dengan slot item, hover effect, dan badge rarity.',
      tags: ['grid', 'items', 'storage'] },
    { id: 'settings',  name: 'Settings Menu',   badges: ['ui', 'logic'], category: 'menu',
      desc: 'Menu pengaturan dengan toggle switch, slider, dan dropdown. Toggle tersimpan per pemain.',
      tags: ['form', 'settings', 'config', 'toggle'], logic: 'SetSetting · SaveSettings' },
    { id: 'shop',      name: 'Shop Panel',      badges: ['popular', 'logic'], category: 'commerce',
      desc: 'Toko dengan kartu produk & harga. Game Logic: katalog item + pembelian aman di server.',
      tags: ['shop', 'commerce', 'store', 'buy'], logic: 'Purchase · leaderstats Gems' },
    { id: 'dialogue',  name: 'Dialogue Box',    badges: ['rpg', 'logic'], category: 'rpg',
      desc: 'Kotak dialog RPG dengan avatar, nama speaker, dan pilihan respons.',
      tags: ['rpg', 'dialog', 'npc', 'quest'], logic: 'DialogueChoice' },

    { id: 'daily-reward', name: 'Daily Reward', badges: ['new', 'logic'], category: 'commerce',
      desc: 'Hadiah login 7 hari dengan streak. Tombol Claim dengan cooldown 24 jam di server.',
      tags: ['reward', 'daily', 'claim', 'streak'], logic: 'Claim' },
    { id: 'redeem-codes', name: 'Redeem Codes', badges: ['new', 'logic'], category: 'menu',
      desc: 'Input kode promo + tombol Redeem. Kode & hadiah diatur di GameConfig, sekali pakai per pemain.',
      tags: ['code', 'promo', 'input', 'redeem'], logic: 'Redeem' },
    { id: 'hud',       name: 'Game HUD',        badges: ['new', 'popular'], category: 'hud',
      desc: 'HUD lengkap: avatar, HP/XP bar, saldo koin & gem (live dari leaderstats), hotbar, quest tracker.',
      tags: ['hud', 'health', 'coins', 'hotbar'], logic: 'leaderstats Coins + Gems' },
    { id: 'leaderboard', name: 'Leaderboard',   badges: ['new'], category: 'ui',
      desc: 'Papan peringkat berbasis <table> dengan highlight top 3 dan posisi pemain.',
      tags: ['rank', 'table', 'score', 'top'] },
    { id: 'quest-board', name: 'Quest Board',   badges: ['new', 'rpg', 'logic'], category: 'rpg',
      desc: 'Papan misi bergaya kayu dengan hadiah dan tombol Accept / Decline.',
      tags: ['quest', 'mission', 'rpg', 'npc'], logic: 'AcceptQuest · DeclineQuest' },
    { id: 'gacha',     name: 'Lucky Spin',      badges: ['new', 'logic'], category: 'commerce',
      desc: 'Roda gacha dengan peluang rarity. Hasil diacak di server (anti-cheat).',
      tags: ['gacha', 'spin', 'luck', 'roll'], logic: 'Spin' },
    { id: 'upgrade',   name: 'Forge Upgrade',   badges: ['new', 'logic'], category: 'rpg',
      desc: 'Panel upgrade senjata dengan perbandingan stat, tombol Upgrade & Sell.',
      tags: ['upgrade', 'forge', 'weapon', 'stats'], logic: 'Upgrade · Sell' },
    { id: 'loadout',   name: 'Loadout',         badges: ['new', 'logic'], category: 'rpg',
      desc: 'Grid senjata dengan tombol Equip / Unequip per item.',
      tags: ['equip', 'weapon', 'loadout', 'grid'], logic: 'Equip · Unequip' },
    { id: 'pause-menu', name: 'Pause Menu',     badges: ['new'], category: 'menu',
      desc: 'Menu pause di tengah layar dengan overlay gelap dan tombol Resume / Settings / Leave.',
      tags: ['pause', 'menu', 'overlay'] },
    { id: 'battle-pass', name: 'Battle Pass',   badges: ['new', 'logic'], category: 'commerce',
      desc: 'Track tier season dengan progress bar, reward premium, Buy Premium & Claim All.',
      tags: ['season', 'pass', 'tier', 'premium'], logic: 'Purchase · Claim' },
    { id: 'death-screen', name: 'Death Screen', badges: ['new'], category: 'hud',
      desc: 'Layar kalah dengan statistik ronde, tombol Respawn dan Revive.',
      tags: ['death', 'respawn', 'overlay', 'stats'] },
    { id: 'profile-card', name: 'Profile Card', badges: ['new'], category: 'ui',
      desc: 'Kartu profil pemain dengan cover gradient, badge, statistik, Add Friend & Trade.',
      tags: ['profile', 'player', 'card', 'social'] },
    { id: 'crafting',  name: 'Crafting Table',  badges: ['new', 'logic'], category: 'rpg',
      desc: 'Resep crafting dengan slot bahan (lengkap/kurang) dan hasil item.',
      tags: ['craft', 'recipe', 'materials'], logic: 'Craft' },
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
          ${s.logic ? `<div class="lib-card-logic" title="Otomatis dibuatkan di tab Game Logic">🧠 ${s.logic}</div>` : ''}
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
    try { localStorage.setItem('arrr_load_sample', id); } catch {}
    // URL converter dari server (jalan untuk pretty URL maupun index.php?page=...)
    const target = new URL(window.__converterUrl || 'converter', location.href).href;

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