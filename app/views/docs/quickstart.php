<?php
// docs/quickstart.php
?>
<section class="docs-section" data-doc="quickstart">
  <h1 data-anchor="quickstart">Quick Start <a class="anchor" href="#quickstart">#</a></h1>
  <p>Tiga langkah untuk convert UI pertamamu ke Roblox.</p>

  <h2 data-anchor="qs-paste">1. Paste HTML</h2>
  <p>Di panel <strong>HTML Input</strong>, paste kode HTML/CSS kamu:</p>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> index.html</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>&lt;div style="width:300px;padding:20px;background:#1a1a1a;border-radius:12px;color:#fff;"&gt;
  &lt;h2 style="margin:0 0 10px;"&gt;Hello Roblox&lt;/h2&gt;
  &lt;button style="padding:8px 16px;background:#d4af37;border:none;border-radius:6px;"&gt;
    Click Me
  &lt;/button&gt;
&lt;/div&gt;</code></pre>
  </div>

  <h2 data-anchor="qs-preview">2. Preview</h2>
  <p>Panel tengah menampilkan 2 mode:</p>
  <ul>
    <li><strong>HTML</strong> — persis seperti di browser</li>
    <li><strong>Roblox</strong> — simulasi StarterGui dengan absolute positioning</li>
  </ul>

  <h2 data-anchor="qs-export">3. Export</h2>
  <p>Klik tombol di top bar:</p>
  <table class="docs-table">
    <thead>
      <tr><th>Format</th><th>Kegunaan</th></tr>
    </thead>
    <tbody>
      <tr><td><code>RBXMX</code></td><td>File XML — import manual via File → Import</td></tr>
      <tr><td><code>LUA</code></td><td>Script Lua lengkap — paste ke LocalScript</td></tr>
      <tr><td><code>Plugin</code></td><td>Plugin auto-import — install sekali, pakai selamanya</td></tr>
    </tbody>
  </table>

  <div class="callout warn">
    <span class="callout-icon">⚠️</span>
    <div>
      <strong>Perhatian:</strong> Canvas default 800×600 px. Elemen yang keluar
      dari area ini akan di-crop saat convert.
    </div>
  </div>
</section>