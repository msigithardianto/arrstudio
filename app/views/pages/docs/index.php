<?php
// app/views/pages/docs/index.php
?>
<div class="reading-progress" id="readingProgress"></div>
<div class="docs-overlay" id="docsOverlay"></div>

<main class="docs-wrap">
  <?php View::partial('pages/docs/sections/sidebar'); ?>

  <div class="docs-main" id="docsMain">
    <div class="docs-inner">
      <?php View::partial('pages/docs/sections/intro'); ?>
      <?php View::partial('pages/docs/sections/quickstart'); ?>
      <?php View::partial('pages/docs/sections/html'); ?>
      <?php View::partial('pages/docs/sections/actions'); ?>
      <?php View::partial('pages/docs/sections/output'); ?>
      <?php View::partial('pages/docs/sections/plugin'); ?>
      <?php View::partial('pages/docs/sections/api'); ?>
      <?php View::partial('pages/docs/sections/faq'); ?>
    </div>
  </div>
</main>