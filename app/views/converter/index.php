<?php
// pages/converter.php — konten halaman converter
?>
<script>
// ============================================================
// Guest limit config
// ============================================================
window.__isLoggedIn = <?= Auth::check() ? 'true' : 'false' ?>;
window.__oauthUrls = {
  google:  '<?= url('auth_google') ?>',
  discord: '<?= url('auth_discord') ?>',
};
window.__docsUrl    = '<?= url('docs') ?>';
window.__libraryUrl = '<?= url('library') ?>';
</script>
<main class="app-main">

  <!-- ============================================================
       PANEL INPUT
  ============================================================ -->
  <section class="panel" id="panelInput">
    <div class="panel-head">
        <span class="dot orange"></span>
        <span class="label">HTML Input</span>
        <div class="head-actions">
        <button onclick="loadSample('sidebar')" class="chip">Sidebar</button>
        <button onclick="loadSample('music')" class="chip">Music</button>
        <button onclick="clearInput()" class="chip">Clear</button>
        </div>
    </div>

    <div class="vscode-editor">
        <div class="vscode-tabs">
        <div class="vscode-tab active">
            <span class="vscode-tab-icon">🌐</span>
            <span class="vscode-tab-name">index.html</span>
            <span class="vscode-tab-close">×</span>
        </div>
        <div class="vscode-tab-actions">
            <button class="vscode-tab-action" id="btnToggleMinimap" title="Toggle Minimap">▥</button>
            <button class="vscode-tab-action" title="Split Editor">⬒</button>
            <button class="vscode-tab-action" title="More Actions">⋯</button>
        </div>
        </div>

        <div class="vscode-body">
        <div class="vscode-gutter" id="vscodeGutter">
            <div class="vscode-line-num">1</div>
        </div>

        <div class="vscode-code-wrap">
            <pre class="vscode-highlight" id="vscodeHighlight" aria-hidden="true"></pre>
            <textarea
            id="htmlIn"
            class="vscode-input"
            spellcheck="false"
            autocomplete="off"
            autocorrect="off"
            autocapitalize="off"
            placeholder="Paste HTML di sini..."
            ></textarea>
        </div>

        <div class="vscode-minimap" id="vscodeMinimap">
            <div class="vscode-minimap-content" id="vscodeMinimapContent"></div>
            <div class="vscode-minimap-viewport" id="vscodeMinimapViewport"></div>
        </div>
        </div>
    </div>

    <div class="panel-foot vscode-status">
        <div class="vscode-status-left">
        <span class="vscode-branch">⎇ main</span>
        <span class="vscode-status-item">
            <span id="vscodeErrors">0</span>
            <span class="vscode-icon-err">⊗</span>
        </span>
        <span class="vscode-status-item">
            <span id="vscodeWarnings">0</span>
            <span class="vscode-icon-warn">⚠</span>
        </span>
        </div>
        <div class="vscode-status-right">
        <span class="vscode-status-item" id="vscodeCursor">Ln 1, Col 1</span>
        <span class="vscode-status-item">Spaces: 2</span>
        <span class="vscode-status-item">UTF-8</span>
        <span class="vscode-status-item">HTML</span>
        </div>
    </div>
  </section>

  <div class="resizer" data-target="input"></div>

  <!-- ============================================================
       PANEL PREVIEW
  ============================================================ -->
  <section class="panel" id="panelPreview">
    <div class="panel-head">
      <span class="dot green"></span>
      <span class="label">Live Preview</span>
      <div class="head-actions">
        <div class="view-toggle">
          <button id="viewHTML" class="active" onclick="setViewMode('html')">HTML</button>
          <button id="viewRBX" onclick="setViewMode('roblox')">Roblox</button>
        </div>
        <button onclick="zoomOut()" class="icon-btn">−</button>
        <span id="zoomLabel" class="mono zoom-label">100%</span>
        <button onclick="zoomIn()" class="icon-btn">+</button>
        <button onclick="fitToScreen()" class="icon-btn">⛶</button>
      </div>
    </div>
    <div id="previewStage">
      <div id="previewFrameWrap">
        <iframe id="previewFrame" sandbox="allow-same-origin allow-scripts" scrolling="no"></iframe>
      </div>
      <div id="robloxFrameWrap" class="hidden">
        <div id="robloxCanvas"></div>
      </div>
    </div>
    <div class="panel-foot">
      <span>Output StarterGui</span>
      <span class="mono">800 × 600</span>
    </div>
  </section>

  <div class="resizer" data-target="output"></div>

  <!-- ============================================================
       PANEL OUTPUT
  ============================================================ -->
  <section class="panel" id="panelOutput">
    <div class="panel-head">
      <span class="dot blue"></span>
      <span class="label">Output</span>
      <span id="luaLines" class="mono">0 lines</span>
    </div>

    <div class="tabs">
      <button data-tab="script"     class="tab active" onclick="switchTab('script')">Behavior Script</button>
      <button data-tab="fullscript" class="tab"        onclick="switchTab('fullscript')">Full Lua</button>
      <button data-tab="tree"       class="tab"        onclick="switchTab('tree')">Tree</button>
      <button data-tab="rbxmx"      class="tab"        onclick="switchTab('rbxmx')">RBXMX</button>
      <button data-tab="plugin"     class="tab"        onclick="switchTab('plugin')">Plugin</button>
      <button data-tab="billboard"  class="tab"        onclick="switchTab('billboard')">👑 Billboard</button>
      <button data-tab="report"     class="tab"        onclick="switchTab('report')">Report</button>
    </div>

    <!-- BILLBOARD CONFIG PANEL -->
    <div id="billboardConfig" class="billboard-config hidden">

      <div class="bb-intro">
        <span class="bb-intro-icon">👑</span>
        <div>
          <div><strong>Billboard Nametag</strong> — script untuk nama overhead di atas kepala player.</div>
          <div class="bb-intro-sub">Ubah config di bawah, preview akan update otomatis. Klik Generate lalu Copy ke LocalScript di StarterPlayerScripts.</div>
        </div>
      </div>

      <div class="bb-field">
        <label class="bb-label">Player Name <span class="help" data-tip="Nama yang ditampilkan. Default: 'Player'">?</span></label>
        <input type="text" id="bbName" value="Player" placeholder="Steve">
      </div>

      <div class="bb-field">
        <label class="bb-label">Role / Rank <span class="help" data-tip="Text role di bawah nama. Contoh: '[O] Owners'">?</span></label>
        <input type="text" id="bbRole" value="Member" placeholder="[O] Owners">
      </div>

      <div class="bb-field">
        <label class="bb-label">Level <span class="help" data-tip="Level player. Auto-update dari leaderstats.Level jika ada">?</span></label>
        <input type="number" id="bbLevel" value="1" min="1" max="9999">
      </div>

      <div class="bb-field">
        <label class="bb-label">Offset Y (studs) <span class="help" data-tip="Jarak di atas kepala dalam studs. 3.5 = default">?</span></label>
        <input type="number" id="bbOffsetY" value="3.5" step="0.1" min="1" max="10">
      </div>

      <div class="bb-field full">
        <label class="bb-label">Theme <span class="help" data-tip="Skema warna nametag. Pilih salah satu">?</span></label>
        <div class="bb-theme-swatches" id="bbThemeSwatches">
          <div class="bb-theme-swatch gold active" data-theme="gold">Gold</div>
          <div class="bb-theme-swatch blue" data-theme="blue">Blue</div>
          <div class="bb-theme-swatch purple" data-theme="purple">Purple</div>
        </div>
        <input type="hidden" id="bbTheme" value="gold">
      </div>

      <div class="bb-field">
        <label class="bb-label">Max Distance <span class="help" data-tip="Jarak max nametag kelihatan. 120 = default">?</span></label>
        <input type="number" id="bbMaxDistance" value="120" min="20" max="500">
      </div>

      <div class="bb-field">
        <label class="bb-label">Player <span class="help" data-tip="Local = untuk semua player. All = termasuk kamu">?</span></label>
        <select id="bbScope">
          <option value="others">Others Only (skip local)</option>
          <option value="all">All Players</option>
        </select>
      </div>

      <div class="bb-preview">
        <div class="bb-preview-label">👁️ Live Preview</div>
        <div class="bb-visual" id="bbVisual">
          <div class="bb-badges">
            <div class="bb-badge mic">🎤</div>
            <div class="bb-badge chat">💬</div>
            <div class="bb-badge crown">👑</div>
          </div>
          <div class="bb-name" id="bbVisualName" style="color: #f4d03f;">Player</div>
          <div class="bb-role" id="bbVisualRole" style="color: #c0c0c8;">Member</div>
          <div class="bb-level" id="bbVisualLevel" style="color: #22c55e;">Level 1</div>
        </div>
      </div>

      <div class="bb-actions">
        <button type="button" class="bb-btn bb-btn-primary" onclick="regenerateBillboard()">⚡ Generate Billboard Script</button>
        <button type="button" class="bb-btn bb-btn-ghost" onclick="copyBillboard()" title="Copy script">📋 Copy</button>
        <button type="button" class="bb-btn bb-btn-ghost" onclick="downloadFile('billboard')" title="Download .lua">⬇ Download</button>
      </div>

      <div class="bb-status" id="bbStatus">
        <span class="bb-status-dot"></span>
        <span id="bbStatusText">Siap. Konfigurasi akan auto-generate.</span>
      </div>

    </div>

    <div class="plugin-action-bar hidden" id="pluginActionBar">
      <button class="plugin-action-btn" onclick="downloadFile('plugin')">⬇ Download Plugin (.lua)</button>
      <button class="plugin-action-btn" onclick="copyCurrent()">📋 Copy Plugin Code</button>
      <button class="plugin-action-btn ghost" onclick="openPluginModal()">❓ Cara Pakai</button>
    </div>

    <div id="outputBody"></div>
    <div class="panel-foot">
      <span id="luaStatus">Ready</span>
      <span class="mono">UTF-8</span>
    </div>
  </section>

  <!-- ============================================================
       PLUGIN MODAL
  ============================================================ -->
  <div class="plugin-modal-overlay" id="pluginModal">
    <div class="plugin-modal">
      <div class="plugin-modal-head">
        <div class="plugin-modal-title">
          <div class="plugin-modal-icon">⚡</div>
          <div>
            <div class="plugin-modal-title-main">Cara Pakai Plugin ARRR Studio</div>
            <div class="plugin-modal-title-sub">Auto-import UI ke Roblox Studio dalam 5 langkah</div>
          </div>
        </div>
        <button class="plugin-modal-close" onclick="closePluginModal()">✕</button>
      </div>

      <div class="plugin-modal-body">

        <div class="plugin-step">
          <div class="plugin-step-num">1</div>
          <div class="plugin-step-content">
            <div class="plugin-step-title">Download file plugin</div>
            <div class="plugin-step-desc">
              Klik tombol <strong>Download Plugin</strong> di bawah, atau copy kode plugin
              dari tab <code>Plugin</code> di panel Output. Simpan sebagai
              <code>ArrStudioImporter.lua</code>.
            </div>
          </div>
        </div>

        <div class="plugin-step">
          <div class="plugin-step-num">2</div>
          <div class="plugin-step-content">
            <div class="plugin-step-title">Buka folder Plugins Roblox Studio</div>
            <div class="plugin-step-desc">
              Di Roblox Studio: tab <strong>Plugins</strong> → <strong>Plugins Folder</strong>.
              Atau buka manual:
            </div>
            <div class="plugin-path-box">
              <span class="path-text" id="pluginPathWin">%LOCALAPPDATA%\Roblox\Plugins</span>
              <button class="plugin-path-copy" onclick="copyPluginPath('pluginPathWin')">Copy</button>
            </div>
            <div class="plugin-path-box" style="margin-top:6px;">
              <span class="path-text" id="pluginPathMac">~/Documents/Roblox/Plugins</span>
              <button class="plugin-path-copy" onclick="copyPluginPath('pluginPathMac')">Copy</button>
            </div>
          </div>
        </div>

        <div class="plugin-step">
          <div class="plugin-step-num">3</div>
          <div class="plugin-step-content">
            <div class="plugin-step-title">Copy file ke folder Plugins</div>
            <div class="plugin-step-desc">
              Paste file <code>ArrStudioImporter.lua</code> ke folder yang terbuka.
              Pastikan ekstensi file <code>.lua</code> (bukan <code>.txt</code>).
            </div>
          </div>
        </div>

        <div class="plugin-step">
          <div class="plugin-step-num">4</div>
          <div class="plugin-step-content">
            <div class="plugin-step-title">Restart Roblox Studio</div>
            <div class="plugin-step-desc">
              <strong>Tutup total</strong> Studio (bukan minimize), lalu buka lagi.
              Plugin akan auto-load saat Studio start.
            </div>
          </div>
        </div>

        <div class="plugin-step">
          <div class="plugin-step-num">5</div>
          <div class="plugin-step-content">
            <div class="plugin-step-title">Pakai plugin</div>
            <div class="plugin-step-desc">
              Buka tab <strong>Plugins</strong> di toolbar atas → klik <strong>ARRR Studio</strong>
              → <strong>Import UI</strong>. Widget akan muncul. Paste Lua script hasil convert
              dari sini, lalu klik <strong>🔨 Build</strong>.
            </div>
          </div>
        </div>

        <div class="plugin-callout">
          <span class="plugin-callout-icon">⚠️</span>
          <div>
            <strong>Penting:</strong> Plugin butuh <code>loadstring</code> yang cuma
            jalan kalau <strong>Enable Studio Access to API Services</strong> diaktifkan.
            Buka: <code>Home → Game Settings → Security</code> → centang opsi itu → Save → Restart Studio.
          </div>
        </div>

        <div class="plugin-callout" style="background: linear-gradient(90deg, rgba(96, 165, 250, 0.08), rgba(96, 165, 250, 0.02)); border-left-color: #60a5fa;">
          <span class="plugin-callout-icon">💡</span>
          <div>
            <strong>Alternatif tanpa plugin:</strong> Pakai file <code>.rbxmx</code> —
            download dari tombol RBXMX, lalu import manual via
            <code>File → Import</code> di Roblox Studio. Hasilnya sama.
          </div>
        </div>

      </div>

      <div class="plugin-modal-foot">
        <button class="plugin-modal-btn primary" onclick="downloadFile('plugin'); closePluginModal();">
          ⬇ Download Plugin Sekarang
        </button>
        <button class="plugin-modal-btn ghost" onclick="closePluginModal()">
          Tutup
        </button>
      </div>
    </div>
  </div>

  <!-- TOAST -->
  <div class="toast" id="toast">
    <span class="toast-icon" id="toastIcon">✓</span>
    <span id="toastText">Done</span>
  </div>

</main>