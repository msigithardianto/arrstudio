<?php
// app/views/pages/errors/404.php — halaman 404 custom
?>
<main class="app-main error-page">
  <div class="error-card">
    <div class="error-code">404</div>
    <div class="error-title">Halaman tidak ditemukan</div>
    <div class="error-desc">
      URL yang kamu akses nggak ada, atau udah dipindah.
    </div>
    <a href="<?= url('converter') ?>" class="error-btn">
      ← Balik ke Converter
    </a>
  </div>
</main>
