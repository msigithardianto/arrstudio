<?php
// views/docs/index.php
?>
<div class="reading-progress" id="readingProgress"></div>
<div class="docs-overlay" id="docsOverlay"></div>

<main class="docs-wrap">
  <?php View::partial('docs/_sidebar'); ?>

  <div class="docs-main" id="docsMain">
    <div class="docs-inner">
      <?php View::partial('docs/intro'); ?>
      <?php View::partial('docs/quickstart'); ?>
      <?php View::partial('docs/html'); ?>
      <?php View::partial('docs/actions'); ?>
      <?php View::partial('docs/output'); ?>
      <?php View::partial('docs/plugin'); ?>
      <?php View::partial('docs/api'); ?>
      <?php View::partial('docs/faq'); ?>
    </div>
  </div>
</main>