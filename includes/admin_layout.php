<?php
if (!isset($adminPageTitle)) {
    $adminPageTitle = 'Admin Panel';
}
?>
<main id="main-content" class="container">
    <div class="admin-layout">
        <aside class="admin-sidebar" aria-label="Admin navigation">
            <a href="<?= e(url('/admin/dashboard.php')) ?>">Dashboard</a>
            <a href="<?= e(url('/admin/processes/index.php')) ?>">Processes</a>
            <a href="<?= e(url('/admin/equipment/index.php')) ?>">Equipment</a>
            <a href="<?= e(url('/admin/chemicals/index.php')) ?>">Chemicals</a>
            <a href="<?= e(url('/admin/videos/index.php')) ?>">Videos</a>
            <a href="<?= e(url('/admin/news/index.php')) ?>">News</a>
            <a href="<?= e(url('/admin/glossary/index.php')) ?>">Glossary</a>
            <a href="<?= e(url('/admin/users/index.php')) ?>">Users</a>
            <a href="<?= e(url('/admin/settings/index.php')) ?>">Settings</a>
        </aside>
        <section class="admin-content">
