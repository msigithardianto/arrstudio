<?php
// app/views/pages/docs/sections/html.php
?>
<section class="docs-section" data-doc="html">
  <h1 data-anchor="html">HTML Support <a class="anchor" href="#html">#</a></h1>
  <p>Tag HTML dan CSS berikut didukung oleh converter.</p>

  <h2 data-anchor="html-tags">Tags → Roblox Class</h2>
  <table class="docs-table">
    <thead>
      <tr><th>HTML Tag</th><th>Roblox Class</th></tr>
    </thead>
    <tbody>
      <tr><td><code>div</code>, <code>section</code>, <code>header</code></td><td><strong>Frame</strong></td></tr>
      <tr><td><code>span</code>, <code>p</code>, <code>h1</code>–<code>h6</code></td><td><strong>TextLabel</strong></td></tr>
      <tr><td><code>button</code></td><td><strong>TextButton</strong></td></tr>
      <tr><td><code>input</code>, <code>textarea</code></td><td><strong>TextBox</strong></td></tr>
      <tr><td><code>img</code></td><td><strong>ImageLabel</strong></td></tr>
      <tr><td><code>ul</code>, <code>ol</code>, <code>li</code></td><td><strong>Frame + TextLabel</strong></td></tr>
    </tbody>
  </table>

  <h2 data-anchor="html-css">CSS Properties</h2>
  <ul>
    <li><code>background</code> — solid & gradient</li>
    <li><code>border-radius</code>, <code>border</code></li>
    <li><code>color</code>, <code>font-size</code>, <code>font-weight</code></li>
    <li><code>padding</code>, <code>margin</code></li>
    <li><code>position</code>, <code>left</code>, <code>top</code>, <code>width</code>, <code>height</code></li>
    <li><code>opacity</code>, <code>visibility</code></li>
  </ul>

  <div class="callout info">
    <span class="callout-icon">ℹ️</span>
    <div>
      <strong>Note:</strong> Tag di luar daftar akan di-skip, tapi children-nya
      tetap diproses secara rekursif.
    </div>
  </div>
</section>