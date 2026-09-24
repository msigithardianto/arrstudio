<?php
// app/views/components/plugin-modal.php — modal cara pakai plugin (buka: openPluginModal())
?>
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
