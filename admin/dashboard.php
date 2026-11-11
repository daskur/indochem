<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
require_admin();

$tables = [
    'Users' => 'users',
    'Processes' => 'processes',
    'Equipment' => 'equipment',
    'Chemicals' => 'chemicals',
    'Videos' => 'videos',
    'Quizzes' => 'quizzes',
    'News' => 'news',
    'Certificates' => 'certificates',
];
$stats = [];
foreach ($tables as $label => $table) {
    $stats[$label] = (int) $pdo->query('SELECT COUNT(*) FROM ' . $table)->fetchColumn();
}

$pageTitle = 'Admin Dashboard';
$isAdmin = true;
require dirname(__DIR__) . '/includes/header.php';
$adminPageTitle = $pageTitle;
require dirname(__DIR__) . '/includes/admin_layout.php';
?>
<header class="module-heading">
    <div><span class="section-kicker">Administration</span><h1>Admin Dashboard</h1></div>
</header>
<div class="stat-grid">
    <?php foreach ($stats as $label => $count): ?>
        <article class="card"><div class="card-body"><strong><?= e((string) $count) ?></strong><span><?= e($label) ?></span></div></article>
    <?php endforeach; ?>
</div>
<section class="admin-quick-actions">
    <h2>Quick actions</h2>
    <div>
        <a class="button button-primary" href="<?= e(url('/admin/processes/index.php')) ?>">Add or manage processes</a>
        <a class="button button-secondary" href="<?= e(url('/admin/news/index.php')) ?>">Manage news</a>
        <a class="button button-secondary" href="<?= e(url('/admin/users/index.php')) ?>">Manage users</a>
        <a class="button button-secondary" href="<?= e(url('/admin/settings/index.php')) ?>">Change password / settings</a>
    </div>
</section>
<?php
require dirname(__DIR__) . '/includes/admin_layout_end.php';
require dirname(__DIR__) . '/includes/footer.php';
?>
