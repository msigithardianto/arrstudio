<?php
// app/views/pages/library/index.php
?>
<main class="page-wrap">
  <div class="page-inner">

    <div class="page-hero">
      <div class="page-hero-eyebrow">Component Library</div>
      <div class="page-title">Template UI Siap Pakai</div>
      <div class="page-sub">
        Pilih template, klik <strong>Open</strong> untuk load langsung ke Converter.
        Semua template sudah dioptimasi untuk konversi ke Roblox StarterGui.
      </div>
    </div>

    <div class="lib-toolbar">
      <div class="lib-search-wrap">
        <input type="text" class="lib-search" id="libSearch"
          placeholder="Cari template... (nama, tag, deskripsi)" autocomplete="off">
      </div>
      <div class="lib-filters" id="libFilters">
        <button class="lib-filter active" data-filter="all">All</button>
        <button class="lib-filter" data-filter="layout">Layout</button>
        <button class="lib-filter" data-filter="ui">UI</button>
        <button class="lib-filter" data-filter="rpg">RPG</button>
        <button class="lib-filter" data-filter="commerce">Shop</button>
      </div>
    </div>

    <div class="lib-grid" id="libGrid"></div>
  </div>
</main>