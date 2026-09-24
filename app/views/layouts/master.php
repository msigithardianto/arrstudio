<!DOCTYPE html>
<html lang="id" class="dark">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle ?? 'ARRR Studio') ?></title>

<link rel="stylesheet" href="<?= asset('app.css') ?>">

<?php View::partial('partials/styles'); ?>
<?php View::partial('partials/styles-nav'); ?>
<?php View::partial('partials/loader-styles'); ?>

<?php if (!empty($extraStyles)): ?>
  <?php foreach ((array)$extraStyles as $style): ?>
    <?php View::partial($style); ?>
  <?php endforeach; ?>
<?php endif; ?>

</head>
<body>

<?php View::partial('partials/loader'); ?>

<?php View::partial('layouts/nav', [
    'activePage' => $activePage ?? 'converter',
    'navVariant' => $navVariant ?? 'app',
]); ?>

<?= $content ?>

<?php View::partial('layouts/footer', [
    'extraScripts' => $extraScripts ?? [],
]); ?>

</body>
</html>