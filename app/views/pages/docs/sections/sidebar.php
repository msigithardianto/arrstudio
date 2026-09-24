<?php
// app/views/pages/docs/sections/sidebar.php
?>
<aside class="docs-side" id="docsSide">
  <div class="docs-side-header">
    <div class="docs-side-label">Documentation</div>
    <input
      type="text"
      class="docs-search"
      id="docsSearch"
      placeholder="Cari dokumentasi..."
      autocomplete="off"
    >
  </div>

  <div class="docs-side-title">Getting Started</div>
  <a class="docs-link active" data-doc="intro">
    <span class="docs-link-icon">🏠</span> Introduction
  </a>
  <a class="docs-link" data-doc="quickstart">
    <span class="docs-link-icon">⚡</span> Quick Start
  </a>

  <div class="docs-side-title">Reference</div>
  <a class="docs-link" data-doc="html">
    <span class="docs-link-icon">📄</span> HTML & CSS
  </a>
  <a class="docs-link" data-doc="actions">
    <span class="docs-link-icon">🎯</span> Actions & Toggle
  </a>
  <a class="docs-link" data-doc="logic">
    <span class="docs-link-icon">🧠</span> Game Logic
    <span class="docs-link-badge">New</span>
  </a>
  <a class="docs-link" data-doc="output">
    <span class="docs-link-icon">📦</span> Output Files
  </a>

  <div class="docs-side-title">Advanced</div>
  <a class="docs-link" data-doc="plugin">
    <span class="docs-link-icon">🔌</span> Plugin & Install Pack
  </a>
  <a class="docs-link" data-doc="api">
    <span class="docs-link-icon">🔌</span> API Reference
  </a>
  <a class="docs-link" data-doc="faq">
    <span class="docs-link-icon">❓</span> FAQ
  </a>

  <div class="docs-no-results" id="docsNoResults">
    Tidak ada hasil untuk pencarian ini.
  </div>
</aside>