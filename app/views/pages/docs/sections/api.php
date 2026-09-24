<?php
// app/views/pages/docs/sections/api.php
?>
<section class="docs-section" data-doc="api">
  <h1 data-anchor="api">API Reference <a class="anchor" href="#api">#</a></h1>
  <p>Kalau mau integrasi dengan tool lain, pakai endpoint berikut.</p>

  <h2 data-anchor="api-convert">POST /api/convert.php</h2>
  <p>Convert HTML + rectMap jadi struktur node.</p>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> JSON Request</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>{
  "html": "&lt;div&gt;...&lt;/div&gt;",
  "rectMap": [
    { "idx": 0, "x": 0, "y": 0, "w": 100, "h": 50 }
  ]
}</code></pre>
  </div>

  <h2 data-anchor="api-generate">POST /api/generate.php</h2>
  <p>Generate Lua / RBXMX / Plugin dari node.</p>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> JSON Request</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>{
  "nodes": [ ... ],
  "canvasW": 800,
  "canvasH": 600
}</code></pre>
  </div>

  <h2 data-anchor="api-response">Response</h2>
  <div class="code-block">
    <div class="code-header">
      <div class="code-lang"><span class="code-dot"></span> JSON Response</div>
      <button class="code-copy" data-copy>📋 Copy</button>
    </div>
    <pre><code>{
  "script":     "-- behavior script",
  "fullscript": "-- full lua",
  "tree":       "StarterGui\n  Frame\n    ...",
  "rbxmx":      "&lt;roblox ...&gt;",
  "plugin":     "-- plugin lua",
  "report":     "Conversion report"
}</code></pre>
  </div>
</section>