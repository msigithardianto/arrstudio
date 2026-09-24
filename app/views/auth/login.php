<?php
// app/views/auth/login.php
?>
<main class="auth">
  <div class="auth-bg-glow"></div>

  <!-- ============ 2 GRID PREMIUM ============ -->
  <div class="auth-grid">

    <!-- GRID 1: BRANDING / SHOWCASE -->
    <section class="auth-showcase">
      <div class="showcase-glow"></div>

      <a href="<?= url('landing') ?>" class="auth-logo">
        <span class="brand-icon">
          <img src="<?= asset('logo.png') ?>" alt="ARRR Studio">
        </span>
        <span class="brand-text">
          <span class="brand-title">ARRR <span class="brand-accent">Studio</span></span>
          <span class="brand-sub">Game Development Suite</span>
        </span>
      </a>

      <div class="showcase-content">
        <span class="showcase-badge">✦ Premium Game Studio</span>
        <h1 class="showcase-title">
          Bangun Dunia<br>
          <span class="gradient-text">Game Impianmu.</span>
        </h1>
        <p class="showcase-desc">
          Platform kolaborasi developer, artist, dan kreator game.
          Kelola project, aset, dan tim dalam satu studio digital.
        </p>

        <div class="showcase-stats">
          <div class="stat-item">
            <span class="stat-value">12K+</span>
            <span class="stat-label">Creator</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">3.4K</span>
            <span class="stat-label">Project</span>
          </div>
          <div class="stat-item">
            <span class="stat-value">98%</span>
            <span class="stat-label">Rating</span>
          </div>
        </div>
      </div>

      <div class="showcase-footer">
        <span>© <?= date('Y') ?> ARRR Studio</span>
      </div>
    </section>

    <!-- GRID 2: LOGIN PANEL -->
    <section class="auth-panel">
      <div class="auth-card">

        <div class="panel-header">
          <span class="panel-tag">AUTH</span>
          <h2 class="auth-title">Masuk ke Studio</h2>
          <p class="auth-sub">Pilih metode login di bawah ini.</p>
        </div>

        <?php if (!empty($error)): ?>
          <div class="auth-error">
            <span>⚠️</span>
            <span><?= e($error) ?></span>
          </div>
        <?php endif; ?>

        <div class="auth-oauth">
          <a href="<?= url('auth_google') ?>" class="auth-oauth-btn auth-oauth-google">
            <svg width="20" height="20" viewBox="0 0 48 48">
              <path fill="#FFC107" d="M43.611,20.083H42V20H24v8h11.303c-1.649,4.657-6.08,8-11.303,8c-6.627,0-12-5.373-12-12c0-6.627,5.373-12,12-12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C12.955,4,4,12.955,4,24c0,11.045,8.955,20,20,20c11.045,0,20-8.955,20-20C44,22.659,43.862,21.35,43.611,20.083z"/>
              <path fill="#FF3D00" d="M6.306,14.691l6.571,4.819C14.655,15.108,18.961,12,24,12c3.059,0,5.842,1.154,7.961,3.039l5.657-5.657C34.046,6.053,29.268,4,24,4C16.318,4,9.656,8.337,6.306,14.691z"/>
              <path fill="#4CAF50" d="M24,44c5.166,0,9.86-1.977,13.409-5.192l-6.19-5.238C29.211,35.091,26.715,36,24,36c-5.202,0-9.619-3.317-11.283-7.946l-6.522,5.025C9.505,39.556,16.227,44,24,44z"/>
              <path fill="#1976D2" d="M43.611,20.083H42V20H24v8h11.303c-0.792,2.237-2.231,4.166-4.087,5.571c0.001-0.001,0.002-0.001,0.003-0.002l6.19,5.238C36.971,39.205,44,34,44,24C44,22.659,43.862,21.35,43.611,20.083z"/>
            </svg>
            <span>Lanjutkan dengan Google</span>
          </a>

          <a href="<?= url('auth_discord') ?>" class="auth-oauth-btn auth-oauth-discord">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
            </svg>
            <span>Lanjutkan dengan Discord</span>
          </a>
        </div>

        <div class="auth-foot">
          Dengan masuk, kamu setuju dengan <a href="#">Terms</a> & <a href="#">Privacy</a> kami.
        </div>
      </div>

      <div class="auth-footer">
        <a href="<?= url('landing') ?>">← Balik ke landing</a>
      </div>
    </section>

  </div>
</main>