<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/database.php';

$user = current_user();
$pageTitle = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="container" style="padding-block:48px">
    <span class="badge"><?= e(ucfirst($user['role'])) ?></span>
    <h1>Welcome, <?= e($user['full_name']) ?></h1>
    <p>Continue your industrial learning journey from your dashboard.</p>
    <div class="dashboard-actions">
        <a class="card card-body" href="<?= e(url('/processes/index.php')) ?>">
            <strong>Explore Processes</strong>
            <p>Browse manufacturing processes step by step.</p>
        </a>
        <a class="card card-body" href="<?= e(url('/profile/index.php')) ?>">
            <strong>Learning Profile</strong>
            <p>Review progress, bookmarks and certificates.</p>
        </a>
    </div>
    <?php if (($user['role'] ?? '') === 'admin'): ?>
        <p style="margin-top:24px"><a class="button button-primary" href="<?= e(url('/admin/dashboard.php')) ?>">Open Admin Panel</a></p>
    <?php endif; ?>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
