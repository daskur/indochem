<?php
require_once dirname(__DIR__) . '/config/functions.php';

$pageBackgroundImages = ['b.jpeg', 'c.jpeg', 'd.jpeg', 'e.jpeg', 'g.jpeg', 'h.jpeg', 'i.jpeg'];
$currentPagePath = parse_url($_SERVER['SCRIPT_NAME'] ?? '', PHP_URL_PATH);
$backgroundIndex = abs(crc32(is_string($currentPagePath) ? $currentPagePath : '')) % count($pageBackgroundImages);
$pageBackgroundClass = 'page-background-' . $backgroundIndex;
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#123a5a">
    <title><?= e($pageTitle ?? 'Industrial Process Learning') ?> | <?= e(IPLA_APP_SHORT_NAME) ?></title>
    <link rel="stylesheet" href="<?= e(url('/assets/css/style.css?v=20261111-backgrounds')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/components.css')) ?>">
    <link rel="stylesheet" href="<?= e(url('/assets/css/responsive.css')) ?>">
    <?php if (!empty($isAdmin)): ?>
        <link rel="stylesheet" href="<?= e(url('/assets/css/admin.css')) ?>">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= e(url('/assets/css/responsive-improvements.css')) ?>">
</head>
<body class="<?= e($pageBackgroundClass) ?>">
<a class="skip-link" href="#main-content">Skip to main content</a>
<?php require __DIR__ . '/navbar.php'; ?>
