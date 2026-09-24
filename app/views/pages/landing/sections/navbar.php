<?php
// app/views/pages/landing/sections/navbar.php — navbar landing
?>
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
