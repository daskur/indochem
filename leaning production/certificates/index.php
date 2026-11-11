<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$userId = (int) current_user()['id'];

$statement = $pdo->prepare(
    'SELECT c.*,p.title AS process_title
     FROM certificates c
     LEFT JOIN processes p ON p.id=c.process_id
     WHERE c.user_id=? ORDER BY c.issued_at DESC,c.id DESC'
);
$statement->execute([$userId]);
$certificates = $statement->fetchAll();

$pageTitle = 'My Certificates';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page certificate-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>My Certificates</span>
    </nav>
    <header class="module-heading">
        <div>
            <span class="section-kicker">Achievements</span>
            <h1>My Certificates</h1>
            <p>Certificates earned by completing industrial learning processes.</p>
        </div>
        <span class="module-count"><?= count($certificates) ?> certificate<?= count($certificates) === 1 ? '' : 's' ?></span>
    </header>

    <?php if ($certificates): ?>
        <div class="certificate-grid">
            <?php foreach ($certificates as $certificate): ?>
                <article class="card certificate-card">
                    <div class="card-body">
                        <span class="badge">Completed process</span>
                        <h2><?= e($certificate['process_title'] ?: $certificate['title']) ?></h2>
                        <p>Issued <?= e(date('d M Y', strtotime($certificate['issued_at']))) ?></p>
                        <small><?= e($certificate['certificate_number']) ?></small>
                        <div class="certificate-actions">
                            <a class="button button-primary button-small" href="<?= e(url('/certificates/view.php?id=' . $certificate['id'])) ?>">View certificate</a>
                            <a class="button button-secondary button-small" href="<?= e(url('/certificates/verify.php?number=' . urlencode($certificate['certificate_number']))) ?>">Verify</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No certificates yet</h2>
            <p>Complete every step of a published process to earn your first certificate.</p>
            <a class="button button-primary" href="<?= e(url('/processes/index.php')) ?>">Explore processes</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
