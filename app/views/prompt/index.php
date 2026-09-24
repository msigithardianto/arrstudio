<?php
// views/prompt/index.php
?>
<main class="pg-wrap">
  <div class="pg-inner">

    <div class="pg-hero">
      <div class="pg-hero-icon">🎯</div>
      <div class="pg-hero-title">HTML Prompt Guide</div>
      <div class="pg-hero-sub">
        Prompt generator khusus buat bikin <strong>HTML yang optimal</strong> untuk
        ARRR Studio Converter.
      </div>
    </div>

    <div class="pg-tabs" id="pgTabs">
      <button type="button" class="pg-tab active" data-tab="generator">
        <span class="pg-tab-icon">🎯</span> Prompt Generator
      </button>
      <button type="button" class="pg-tab" data-tab="rules">
        <span class="pg-tab-icon">📏</span> Aturan Converter
      </button>
      <button type="button" class="pg-tab" data-tab="cheatsheet">
        <span class="pg-tab-icon">📌</span> Cheat Sheet
      </button>
      <button type="button" class="pg-tab" data-tab="templates">
        <span class="pg-tab-icon">📋</span> Templates
      </button>
      <button type="button" class="pg-tab" data-tab="tips">
        <span class="pg-tab-icon">💡</span> Tips
      </button>
    </div>

    <?php View::partial('prompt/generator'); ?>
    <?php View::partial('prompt/rules'); ?>
    <?php View::partial('prompt/cheatsheet'); ?>
    <?php View::partial('prompt/templates'); ?>
    <?php View::partial('prompt/tips'); ?>

  </div>
</main>