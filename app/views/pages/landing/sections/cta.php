<?php
// app/views/pages/landing/sections/cta.php — call to action
?>
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
