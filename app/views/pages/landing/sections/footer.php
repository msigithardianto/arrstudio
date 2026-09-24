<?php
// app/views/pages/landing/sections/footer.php — footer landing
?>
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
