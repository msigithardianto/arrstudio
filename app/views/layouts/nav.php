<?php
// app/views/layouts/nav.php
// Variabel: $activePage, $navVariant
$activePage = $activePage ?? 'converter';
$navVariant = $navVariant ?? 'app';

$navItems = [
    ['id' => 'converter', 'label' => 'Converter', 'icon' => '⚡', 'url' => url('converter')],
    ['id' => 'library',   'label' => 'Library',   'icon' => '📦', 'url' => url('library')],
    ['id' => 'docs',      'label' => 'Docs',      'icon' => '📖', 'url' => url('docs')],
    ['id' => 'prompt',    'label' => 'Prompt',    'icon' => '✨', 'url' => url('prompt')],
];

// ==== AMBIL DATA USER ====
$currentUser = Auth::check() ? Auth::user() : null;

if ($currentUser) {
    $userName   = $currentUser['name'] ?? $currentUser['username'] ?? 'User';
    $userHandle = '@' . ($currentUser['username'] ?? 'user');
    $userEmail  = $currentUser['email'] ?? '';
    $userAvatar = $currentUser['avatar'] ?? '';
    $userRole   = ucfirst($currentUser['provider'] ?? 'Member'); // Google / Discord
    $isLoggedIn = true;
} else {
    $userName   = 'Guest';
    $userHandle = 'Not signed in';
    $userEmail  = '';
    $userAvatar = '';
    $userRole   = 'Guest';
    $isLoggedIn = false;
}

// Fallback avatar
$avatarSrc = $userAvatar !== '' ? $userAvatar : asset('logo.png');
?>
<?php if (($navVariant ?? 'app') === 'landing'): ?>
  <?php /* Landing page: no navbar */ ?>
<?php else: ?>
<header class="app-header" id="appHeader">

  <!-- Mobile hamburger -->
  <button class="nav-toggle" id="navToggle" aria-label="Toggle menu">
    <span></span>
    <span></span>
    <span></span>
  </button>

  <!-- Brand -->
  <a href="<?= url('converter') ?>" class="brand" data-spa>
    <div class="brand-icon">
      <img src="<?= asset('logo.png') ?>" alt="ARRR Studio">
    </div>
    <div class="brand-text">
      <div class="brand-title">
        ARRR <span class="brand-accent">STUDIO</span>
      </div>
      <div class="brand-sub">Roblox UI Converter</div>
    </div>
  </a>

  <!-- Navigation -->
  <nav class="top-nav" id="topNav">
    <?php foreach ($navItems as $item): ?>
      <a class="top-nav-item <?= is_active($item['id'], $activePage) ?>"
         href="<?= $item['url'] ?>"
         data-spa>
        <span class="nav-icon"><?= $item['icon'] ?></span>
        <span class="nav-label"><?= $item['label'] ?></span>
        <span class="nav-indicator"></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <!-- Right side -->
  <div class="top-right">

    <!-- Status (hanya di converter) -->
    <?php if ($navVariant === 'app'): ?>
      <div class="status-group">
        <div class="status-pill live" title="Nodes converted">
          <span class="status-dot"></span>
          <span id="badgeNodes">0 nodes</span>
        </div>
        <div class="status-pill warn hidden" id="badgeWarn" title="Warnings">
          <span class="status-dot"></span>
          <span>0 warn</span>
        </div>
      </div>

      <div class="top-divider"></div>

      <!-- Action buttons -->
      <div class="actions">
        <button onclick="downloadFile('rbxmx')" class="btn btn-icon" title="Download .rbxmx">
          <span>⬇</span>
        </button>
        <button onclick="downloadFile('lua')" class="btn btn-icon" title="Download .lua">
          <span>📜</span>
        </button>
        <button onclick="openPluginModal()" class="btn btn-icon btn-gold" title="Cara pakai Plugin">
          <span>🔌</span>
        </button>
        <button onclick="copyCurrent()" id="copyBtn" class="btn btn-icon btn-silver" title="Copy tab aktif">
          <span>📋</span>
        </button>
      </div>

      <div class="top-divider"></div>
    <?php endif; ?>

    <?php if ($isLoggedIn): ?>
      <!-- ===== PROFILE (LOGGED IN) ===== -->
      <div class="profile-chip" id="profileChip" tabindex="0">
        <div class="profile-avatar">
          <img src="<?= e($avatarSrc) ?>" alt="<?= e($userName) ?>">
          <span class="profile-online"></span>
        </div>
        <div class="profile-info">
          <div class="profile-name"><?= e($userName) ?></div>
          <div class="profile-role"><?= e($userRole) ?></div>
        </div>
        <span class="profile-chevron">▾</span>

        <!-- Dropdown -->
        <div class="profile-menu" id="profileMenu">
          <div class="profile-menu-header">
            <div class="profile-menu-avatar">
              <img src="<?= e($avatarSrc) ?>" alt="<?= e($userName) ?>">
            </div>
            <div class="profile-menu-info">
              <div class="profile-menu-name"><?= e($userName) ?></div>
              <div class="profile-menu-email"><?= e($userEmail ?: $userHandle) ?></div>
            </div>
          </div>
          <div class="profile-menu-divider"></div>
          <a href="#" class="profile-menu-item">
            <span class="profile-menu-icon">👤</span> Profile
          </a>
          <a href="#" class="profile-menu-item">
            <span class="profile-menu-icon">⚙️</span> Settings
          </a>
          <a href="#" class="profile-menu-item">
            <span class="profile-menu-icon">🎨</span> Theme
          </a>
          <div class="profile-menu-divider"></div>
          <a href="<?= url('logout') ?>" class="profile-menu-item danger" data-no-spa>
            <span class="profile-menu-icon">🚪</span> Sign out
          </a>
        </div>
      </div>
    <?php else: ?>
      <!-- ===== LOGIN BUTTON (GUEST) ===== -->
      <a href="<?= url('login') ?>" class="btn btn-gold" data-no-spa>
        <span class="btn-icon">→</span>
        <span class="btn-label">Masuk</span>
      </a>
    <?php endif; ?>

  </div>

  <!-- Mobile overlay -->
  <div class="nav-overlay" id="navOverlay"></div>

</header>
<?php endif; ?>