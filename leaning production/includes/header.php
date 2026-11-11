<?php
require_once dirname(__DIR__) . '/config/functions.php';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123a5a">
    <title><?= e($pageTitle ?? 'Industrial Process Learning') ?> | <?= e(IPLA_APP_SHORT_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/components.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/responsive.css')) ?>">
    <?php if (!empty($isAdmin)): ?>
        <link rel="stylesheet" href="<?= e(url('/assets/css/admin.css')) ?>">
    <?php endif; ?>
</head>
<body>
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php require __DIR__ . '/navbar.php'; ?>
