<?php
// app/views/landing/index.php
?>
<main class="rbx">

  <!-- ═══════════ NAVBAR ═══════════ -->
  <nav class="rbx-nav">
    <div class="rbx-nav-inner">
      <a href="<?= url('landing') ?>" class="rbx-logo">
        <span class="rbx-logo-mark">A</span>
        <span class="rbx-logo-text">ARRR <strong>Studio</strong></span>
      </a>

      <div class="rbx-nav-links" id="rbxNavLinks">
        <a href="<?= url('converter') ?>">Converter</a>
        <a href="<?= url('library') ?>">Library</a>
        <a href="<?= url('docs') ?>">Docs</a>
        <a href="<?= url('prompt') ?>">Prompt</a>
      </div>

      <div class="rbx-nav-actions" id="rbxNavActions">
        <?php if (Auth::check()): ?>
          <a href="<?= url('converter') ?>" class="rbx-btn rbx-btn--primary rbx-btn--sm">Buka Studio</a>
        <?php else: ?>
          <a href="<?= url('login') ?>" class="rbx-btn rbx-btn--ghost rbx-btn--sm" data-no-spa>Masuk</a>
          <a href="<?= url('converter') ?>" class="rbx-btn rbx-btn--primary rbx-btn--sm">Coba Gratis</a>
        <?php endif; ?>
      </div>

      <button class="rbx-nav-toggle" id="rbxNavToggle" aria-label="Menu">
        <span></span><span></span><span></span>
      </button>
    </div>
  </nav>

  <!-- ═══════════ HERO ═══════════ -->
  <section class="rbx-hero">
    <div class="rbx-hero-grid">

      <div class="rbx-hero-content">
        <span class="rbx-badge">
          <span class="rbx-badge-dot"></span>
          v3.5 · Baru Rilis
        </span>

        <h1 class="rbx-title">
          Convert <span class="rbx-title-hl">HTML</span> ke Roblox UI dalam hitungan detik.
        </h1>

        <p class="rbx-subtitle">
          Live preview, multi-format export, dan auto Luau handler.
          Bangun UI Roblox dengan workflow web yang sudah kamu kenal.
        </p>

        <div class="rbx-hero-actions">
          <a href="<?= url('converter') ?>" class="rbx-btn rbx-btn--primary rbx-btn--lg">
            Mulai Convert
            <svg viewBox="0 0 16 16" fill="none" width="16" height="16">
              <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
          </a>
          <a href="<?= url('docs') ?>" class="rbx-btn rbx-btn--ghost rbx-btn--lg">
            Lihat Docs
          </a>
        </div>

        <div class="rbx-hero-meta">
          <span class="rbx-meta-item">
            <svg viewBox="0 0 16 16" fill="none" width="14" height="14">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Berjalan di browser
          </span>
          <span class="rbx-meta-item">
            <svg viewBox="0 0 16 16" fill="none" width="14" height="14">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            4 format export
          </span>
          <span class="rbx-meta-item">
            <svg viewBox="0 0 16 16" fill="none" width="14" height="14">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Preview real-time
          </span>
        </div>
      </div>

      <!-- Preview Card -->
      <div class="rbx-hero-preview">
        <div class="rbx-preview">
          <div class="rbx-preview-head">
            <div class="rbx-preview-dots">
              <span></span><span></span><span></span>
            </div>
            <div class="rbx-preview-tabs">
              <button class="rbx-preview-tab active" data-tab="html">index.html</button>
              <button class="rbx-preview-tab" data-tab="rbx">Output.rbxmx</button>
            </div>
            <span class="rbx-preview-live">
              <span class="rbx-preview-live-dot"></span>
              Live
            </span>
          </div>

          <div class="rbx-preview-body" data-pane="html">
            <pre class="rbx-code">&lt;div class="inventory-card"&gt;
  &lt;h1&gt;Player Inventory&lt;/h1&gt;
  &lt;button class="btn-equip"&gt;
    Equip Item
  &lt;/button&gt;
&lt;/div&gt;</pre>
          </div>

          <div class="rbx-preview-body rbx-hidden" data-pane="rbx">
            <div class="rbx-tree">
              <div class="rbx-tree-row rbx-tree-l0">ScreenGui</div>
              <div class="rbx-tree-row rbx-tree-l1">Frame</div>
              <div class="rbx-tree-row rbx-tree-l2">TextLabel</div>
              <div class="rbx-tree-row rbx-tree-l2">TextButton</div>
              <div class="rbx-tree-row rbx-tree-l1">UICorner</div>
            </div>
          </div>

          <div class="rbx-preview-foot">
            <div class="rbx-preview-stats">
              <div class="rbx-preview-stat">
                <span class="rbx-preview-stat-num">4</span>
                <span class="rbx-preview-stat-lbl">Formats</span>
              </div>
              <div class="rbx-preview-stat">
                <span class="rbx-preview-stat-num">12</span>
                <span class="rbx-preview-stat-lbl">Nodes</span>
              </div>
              <div class="rbx-preview-stat">
                <span class="rbx-preview-stat-num">0.8s</span>
                <span class="rbx-preview-stat-lbl">Build</span>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>

  <!-- ═══════════ HOW IT WORKS ═══════════ -->
  <section class="rbx-section">
    <div class="rbx-section-head">
      <h2 class="rbx-h2">Cara kerjanya simpel.</h2>
      <p class="rbx-section-sub">Tiga langkah dari markup ke StarterGui.</p>
    </div>

    <div class="rbx-steps">
      <div class="rbx-step">
        <div class="rbx-step-num">01</div>
        <h3 class="rbx-step-title">Paste HTML/CSS</h3>
        <p class="rbx-step-desc">Tulis atau paste markup yang kamu mau convert ke editor.</p>
      </div>
      <div class="rbx-step-arrow">
        <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
          <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="rbx-step">
        <div class="rbx-step-num">02</div>
        <h3 class="rbx-step-title">Preview Live</h3>
        <p class="rbx-step-desc">Lihat hasilnya langsung di browser. Zoom, pan, atur viewport.</p>
      </div>
      <div class="rbx-step-arrow">
        <svg viewBox="0 0 24 24" fill="none" width="20" height="20">
          <path d="M5 12h14M13 6l6 6-6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
      </div>
      <div class="rbx-step">
        <div class="rbx-step-num">03</div>
        <h3 class="rbx-step-title">Export</h3>
        <p class="rbx-step-desc">Download sebagai .rbxmx, .lua, .json, atau install plugin.</p>
      </div>
    </div>
  </section>

  <!-- ═══════════ COMPARISON ═══════════ -->
  <section class="rbx-section">
    <div class="rbx-section-head">
      <h2 class="rbx-h2">Kerja lebih cepat, hasil lebih rapi.</h2>
      <p class="rbx-section-sub">Tinggalkan cara manual drag-and-drop node di Studio.</p>
    </div>

    <div class="rbx-compare">
      <div class="rbx-compare-card rbx-compare-card--best">
        <div class="rbx-compare-head">
          <span class="rbx-compare-title">Dengan ARRR Studio</span>
        </div>
        <ul class="rbx-compare-list">
          <li><span class="rbx-ok">✓</span><div><strong>Preview real-time</strong><span>Hasil langsung terlihat</span></div></li>
          <li><span class="rbx-ok">✓</span><div><strong>4 format export</strong><span>.rbxmx, .lua, .json, plugin</span></div></li>
          <li><span class="rbx-ok">✓</span><div><strong>Auto Luau handler</strong><span>Hover, click, toggle otomatis</span></div></li>
          <li><span class="rbx-ok">✓</span><div><strong>Billboard nametag</strong><span>Overhead UI generator</span></div></li>
        </ul>
        <div class="rbx-compare-foot">
          <span class="rbx-compare-foot-lbl">Estimasi waktu</span>
          <span class="rbx-compare-foot-val rbx-compare-foot-val--best">&lt; 10 detik</span>
        </div>
      </div>

      <div class="rbx-compare-card">
        <div class="rbx-compare-head">
          <span class="rbx-compare-title">Cara manual</span>
        </div>
        <ul class="rbx-compare-list">
          <li><span class="rbx-no">✕</span><div><strong>Play test manual</strong><span>Harus run game dulu</span></div></li>
          <li><span class="rbx-no">✕</span><div><strong>Save satu per satu</strong><span>Rawan lupa format</span></div></li>
          <li><span class="rbx-no">✕</span><div><strong>Tulis LocalScript</strong><span>Dari nol, tiap komponen</span></div></li>
          <li><span class="rbx-no">✕</span><div><strong>Setup Adornee manual</strong><span>Studs, offset, scale</span></div></li>
        </ul>
        <div class="rbx-compare-foot">
          <span class="rbx-compare-foot-lbl">Estimasi waktu</span>
          <span class="rbx-compare-foot-val">1 – 3 jam</span>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════ FEATURES ═══════════ -->
  <section class="rbx-section">
    <div class="rbx-section-head">
      <h2 class="rbx-h2">Fitur yang beneran kepake.</h2>
      <p class="rbx-section-sub">Bukan gimmick. Setiap fitur mempercepat workflow kamu.</p>
    </div>

    <div class="rbx-features">
      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
        </div>
        <h3>Live Dual-Preview</h3>
        <p>Editor dan visualisasi jalan beriringan. Zoom, pan, viewport switcher.</p>
      </div>

      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"/><polyline points="8 6 2 12 8 18"/></svg>
        </div>
        <h3>Monaco Editor</h3>
        <p>Rasanya seperti VSCode. Syntax highlight, autocomplete, linting.</p>
      </div>

      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
        </div>
        <h3>Flexible Export</h3>
        <p>.rbxmx, .lua, JSON data tree, atau plugin Studio siap pakai.</p>
      </div>

      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>
        </div>
        <h3>Smart Detection</h3>
        <p>Auto map HTML/CSS ke UIListLayout, UIGridLayout, UICorner.</p>
      </div>

      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <h3>Nametag Studio</h3>
        <p>BillboardGui generator dengan distance limit & auto-scale.</p>
      </div>

      <div class="rbx-feature">
        <div class="rbx-feature-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
        </div>
        <h3>Template Library</h3>
        <p>Shop Grid, Inventory, Dialogue Box, HUD — siap pakai.</p>
      </div>
    </div>
  </section>

  <!-- ═══════════ CTA ═══════════ -->
  <section class="rbx-section">
    <div class="rbx-cta">
      <div class="rbx-cta-inner">
        <h2 class="rbx-cta-title">Mulai bangun UI Roblox kamu.</h2>
        <p class="rbx-cta-sub">
          Masuk dengan Google atau Discord untuk menyimpan project dan workflow kamu.
        </p>

        <div class="rbx-cta-actions">
          <?php if (Auth::check()): ?>
            <a href="<?= url('converter') ?>" class="rbx-btn rbx-btn--primary rbx-btn--lg">
              Buka ARRR Studio
              <svg viewBox="0 0 16 16" fill="none" width="16" height="16">
                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
          <?php else: ?>
            <a href="<?= url('login') ?>" class="rbx-btn rbx-btn--primary rbx-btn--lg" data-no-spa>
              Mulai dengan Akun
              <svg viewBox="0 0 16 16" fill="none" width="16" height="16">
                <path d="M3 8h10M9 4l4 4-4 4" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
              </svg>
            </a>
            <a href="<?= url('converter') ?>" class="rbx-btn rbx-btn--ghost rbx-btn--lg">
              Coba Tanpa Akun
            </a>
          <?php endif; ?>
        </div>

        <div class="rbx-cta-note">
          <span class="rbx-cta-note-item">
            <svg viewBox="0 0 16 16" fill="none" width="12" height="12">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Gratis
          </span>
          <span class="rbx-cta-note-item">
            <svg viewBox="0 0 16 16" fill="none" width="12" height="12">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Tanpa kartu kredit
          </span>
          <span class="rbx-cta-note-item">
            <svg viewBox="0 0 16 16" fill="none" width="12" height="12">
              <path d="M13.333 4L6 11.333 2.667 8" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg>
            Login aman via OAuth
          </span>
        </div>
      </div>
    </div>
  </section>

  <!-- ═══════════ FOOTER ═══════════ -->
  <footer class="rbx-footer">
    <div class="rbx-footer-inner">
      <div class="rbx-footer-brand">
        <span class="rbx-logo-mark rbx-logo-mark--sm">A</span>
        <div>
          <div class="rbx-footer-name">ARRR <strong>Studio</strong></div>
          <div class="rbx-footer-tag">Web-to-Roblox UI Pipeline</div>
        </div>
      </div>

      <div class="rbx-footer-links">
        <a href="<?= url('converter') ?>">Converter</a>
        <a href="<?= url('library') ?>">Library</a>
        <a href="<?= url('docs') ?>">Docs</a>
        <a href="<?= url('prompt') ?>">Prompt</a>
      </div>
    </div>

    <div class="rbx-footer-bottom">
      <span>© <?= date('Y') ?> ARRR Studio</span>
      <span class="rbx-footer-sep">·</span>
      <span>Made for Roblox developers</span>
    </div>
  </footer>

</main>

<script>
(function () {
  // Preview tabs
  document.querySelectorAll('.rbx-preview-tab').forEach(tab => {
    tab.addEventListener('click', () => {
      const target = tab.dataset.tab;
      document.querySelectorAll('.rbx-preview-tab').forEach(t => t.classList.toggle('active', t === tab));
      document.querySelectorAll('.rbx-preview-body').forEach(p => {
        p.classList.toggle('rbx-hidden', p.dataset.pane !== target);
      });
    });
  });

  // Nav toggle
  const toggle = document.getElementById('rbxNavToggle');
  const nav = document.querySelector('.rbx-nav');
  if (toggle && nav) {
    toggle.addEventListener('click', () => nav.classList.toggle('rbx-nav--open'));
  }
})();
</script>