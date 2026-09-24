<?php
// app/views/pages/prompt/sections/rules.php — Tab 2: Aturan Converter
?>
<div class="pg-panel" data-panel="rules">
  <div class="pg-section">
    <div class="pg-section-title">
      <span class="num">!</span>
      Aturan Converter ARRR Studio
    </div>
    <div class="pg-section-desc">
      AI harus tau aturan ini biar HTML yang dihasilkan <strong>bisa di-convert</strong> ke Roblox dengan baik.
    </div>

    <div class="pg-section-title" style="margin-top:24px;">
      <span class="num">✅</span>
      Yang HARUS dipakai
    </div>

    <div class="pg-cheat-grid">
      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">📐</span> Layout
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>position: absolute</code> + <code>left</code> + <code>top</code> + <code>width</code> + <code>height</code> (px)<br>
          Parent: <code>position: relative</code><br>
          Canvas: 800 × 600 px
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">📦</span> Tag
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>div</code>, <code>span</code>, <code>p</code>, <code>button</code>,<br>
          <code>input</code>, <code>img</code>, <code>h1</code>-<code>h6</code>,<br>
          <code>ul</code>, <code>ol</code>, <code>li</code>, <code>label</code>,<br>
          <code>section</code>, <code>header</code>, <code>footer</code>
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">🎨</span> Style
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>background</code> (hex / rgb / gradient)<br>
          <code>color</code>, <code>font-size</code> (px), <code>font-weight</code><br>
          <code>border</code>, <code>border-radius</code> (px)<br>
          <code>padding</code>, <code>margin</code> (px)
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">🎯</span> Interaktif
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>data-action="toggle"</code> + <code>data-target="idPanel"</code><br>
          <code>data-action="close"</code> + <code>data-target="idPanel"</code><br>
          Panel tersembunyi: <code>style="display:none;"</code>
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">🖼️</span> Icon / Image
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          Pakai <strong>emoji</strong> (⚔️ 🛡️ 🧪 💰) karena langsung jadi text<br>
          Atau <code>&lt;img&gt;</code> dengan src placeholder
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head">
          <span class="pg-cheat-icon">📏</span> Satuan
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          Selalu pakai <strong>px</strong>, jangan <code>%</code>, <code>em</code>, <code>rem</code>, <code>vh</code>, <code>vw</code>
        </div>
      </div>
    </div>

    <div class="pg-section-title" style="margin-top:32px;">
      <span class="num">❌</span>
      Yang TIDAK BOLEH dipakai
    </div>

    <div class="pg-cheat-grid">
      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Layout Modern
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>display: flex</code><br>
          <code>display: grid</code><br>
          <code>position: fixed</code> / <code>sticky</code>
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Tag Unsupported
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>canvas</code>, <code>svg</code>, <code>video</code>,<br>
          <code>iframe</code>, <code>form</code>, <code>table</code>,<br>
          <code>select</code>, <code>option</code>
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Pseudo-element
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>::before</code>, <code>::after</code><br>
          <code>:hover</code>, <code>:focus</code> (di CSS)<br>
          (bisa pakai <code>:hover</code> cuma buat preview HTML)
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Unit Relative
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>%</code>, <code>em</code>, <code>rem</code><br>
          <code>vh</code>, <code>vw</code>, <code>ch</code><br>
          <code>calc()</code>, <code>clamp()</code>
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Efek Modern
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>box-shadow</code> (glow, blur)<br>
          <code>backdrop-filter</code>, <code>filter</code><br>
          <code>transform</code> (kecuali translate)
        </div>
      </div>

      <div class="pg-cheat-card">
        <div class="pg-cheat-head" style="color:#ef4444;">
          <span class="pg-cheat-icon" style="background:rgba(239,68,68,0.15);">🚫</span> Font Eksternal
        </div>
        <div style="font-size:12.5px;color:var(--text-dim);line-height:1.7;">
          <code>@font-face</code>, Google Fonts<br>
          Roblox cuma punya: Arial, Source Sans, Gotham, dll
        </div>
      </div>
    </div>

    <div class="pg-section" style="margin-top:32px;">
      <div class="pg-section-title">
        <span class="num">⚠️</span>
        Contoh: Jelek vs Bagus
      </div>

      <div class="pg-compare">
        <div class="pg-compare-card bad">
          <div class="pg-compare-head">❌ HTML Jelek (gagal convert)</div>
          <div class="pg-compare-body">&lt;div style="display:flex; gap:10px;"&gt;
  &lt;div style="flex:1; background:#fff;"&gt;
    Item 1
  &lt;/div&gt;
  &lt;div style="flex:1; background:#fff;"&gt;
    Item 2
  &lt;/div&gt;
&lt;/div&gt;</div>
        </div>
        <div class="pg-compare-card good">
          <div class="pg-compare-head">✅ HTML Bagus (convert sukses)</div>
          <div class="pg-compare-body">&lt;div style="position:relative; width:400px; height:100px;"&gt;
  &lt;div style="position:absolute; left:0; top:0;
              width:190px; height:100px;
              background:#ffffff;"&gt;
    Item 1
  &lt;/div&gt;
  &lt;div style="position:absolute; left:210px; top:0;
              width:190px; height:100px;
              background:#ffffff;"&gt;
    Item 2
  &lt;/div&gt;
&lt;/div&gt;</div>
        </div>
      </div>
    </div>
  </div>
</div>