<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$reportId = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$reportId) {
    http_response_code(404);
    exit('Report not found.');
}
$statement = $pdo->prepare('SELECT * FROM siwes_reports WHERE id=? AND user_id=? LIMIT 1');
$statement->execute([$reportId,(int) current_user()['id']]);
$report = $statement->fetch();
if (!$report) {
    http_response_code(404);
    exit('Report not found.');
}

$pageTitle = $report['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container siwes-export-page">
    <article class="siwes-export-document">
        <span class="section-kicker">Industrial Training Report</span>
        <h1><?= e($report['title']) ?></h1>
        <p><strong>Student:</strong> <?= e(current_user()['full_name']) ?></p>
        <p><strong>Report type:</strong> <?= e($report['report_type']) ?></p>
        <p><strong>Status:</strong> <?= e(ucfirst($report['status'])) ?></p>
        <p><strong>Updated:</strong> <?= e(date('d F Y', strtotime($report['updated_at']))) ?></p>
        <hr>
        <div><?= nl2br(e($report['content'] ?: 'No report content has been recorded.')) ?></div>
    </article>
    <div class="certificate-actions certificate-no-print">
        <button class="button button-primary" type="button" onclick="window.print()">Print / Save as PDF</button>
        <a class="button button-secondary" href="<?= e(url('/siwes/report.php?id=' . $report['id'])) ?>">Back to report</a>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
