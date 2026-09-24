<?php
// app/views/pages/landing/sections/hero.php — hero + preview
?>
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
