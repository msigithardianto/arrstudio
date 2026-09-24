<?php
// app/views/pages/docs/sections/actions.php
?>
<section class="docs-section" data-doc="actions">
  <h1 data-anchor="actions">Actions & Toggle <a class="anchor" href="#actions">#</a></h1>
  <p>Bikin UI interaktif dengan atribut <code>data-action</code>.</p>

  <h2 data-anchor="act-toggle">Toggle Panel</h2>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> HTML</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>&lt;button data-action="toggle" data-target="myPanel"&gt;Open&lt;/button&gt;

&lt;div id="myPanel" style="display:none;"&gt;
  Panel content
&lt;/div&gt;</code></pre>
  </div>
  <p>Saat diklik, panel dengan <code>id="myPanel"</code> muncul/hilang dengan animasi slide.</p>

  <h2 data-anchor="act-close">Close Panel</h2>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> HTML</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>&lt;div data-action="close" data-target="myPanel"&gt;×&lt;/div&gt;</code></pre>
  </div>

  <h2 data-anchor="act-rules">Aturan Matching</h2>
  <ul>
    <li><code>data-target</code> harus sama dengan <code>id</code> panel</li>
    <li>Panel harus punya <code>style="display:none"</code> di HTML</li>
    <li>Bisa pakai class selector: <code>data-target=".panel-class"</code></li>
  </ul>
</section>