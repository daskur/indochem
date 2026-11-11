<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$certificateId = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$certificateId) {
    http_response_code(404);
    exit('Certificate not found.');
}

$statement = $pdo->prepare(
    'SELECT c.*,u.full_name,u.email,p.title AS process_title
     FROM certificates c
     JOIN users u ON u.id=c.user_id
     LEFT JOIN processes p ON p.id=c.process_id
     WHERE c.id=? AND c.user_id=? LIMIT 1'
);
$statement->execute([$certificateId,(int) current_user()['id']]);
$certificate = $statement->fetch();
if (!$certificate) {
    http_response_code(404);
    exit('Certificate not found.');
}

$pageTitle = 'Certificate Verification';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container certificate-print-page">
    <nav class="breadcrumbs certificate-no-print" aria-label="Breadcrumb">
        <a href="<?= e(url('/certificates/index.php')) ?>">My Certificates</a><span aria-hidden="true">/</span>
        <span>Certificate</span>
    </nav>
    <article class="certificate-document" aria-label="Certificate of completion">
        <div class="certificate-border">
            <span class="certificate-brand"><?= e(IPLA_APP_SHORT_NAME) ?></span>
            <p class="certificate-kicker">Certificate of Completion</p>
            <h1><?= e($certificate['full_name']) ?></h1>
            <p>has successfully completed the industrial learning process</p>
            <h2><?= e($certificate['process_title'] ?: $certificate['title']) ?></h2>
            <div class="certificate-meta">
                <span>Issued <?= e(date('d F Y', strtotime($certificate['issued_at']))) ?></span>
                <strong><?= e($certificate['certificate_number']) ?></strong>
            </div>
        </div>
    </article>
    <div class="certificate-actions certificate-no-print">
        <button class="button button-primary" type="button" onclick="window.print()">Print / Save as PDF</button>
        <a class="button button-secondary" href="<?= e(url('/certificates/verify.php?number=' . urlencode($certificate['certificate_number']))) ?>">Verify certificate</a>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
