<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$number = strtoupper(trim((string) ($_GET['number'] ?? '')));
$certificate = null;
if ($number !== '') {
    $statement = $pdo->prepare(
        'SELECT c.certificate_number,c.title,c.issued_at,u.full_name,p.title AS process_title
         FROM certificates c
         JOIN users u ON u.id=c.user_id
         LEFT JOIN processes p ON p.id=c.process_id
         WHERE c.certificate_number=? LIMIT 1'
    );
    $statement->execute([$number]);
    $certificate = $statement->fetch();
}

$pageTitle = 'Verify Certificate';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page certificate-verification">
    <header class="module-heading">
        <div><span class="section-kicker">Public verification</span><h1>Verify a Certificate</h1></div>
    </header>
    <form class="certificate-verification-form" method="get" action="<?= e(url('/certificates/verify.php')) ?>">
        <label for="certificate-number">Certificate number</label>
        <div class="certificate-verification-row">
            <input class="form-control" id="certificate-number" name="number" value="<?= e($number) ?>" placeholder="IPLA-YYYYMMDD-XXXXXXXX" maxlength="80" required>
            <button class="button button-primary" type="submit">Verify</button>
        </div>
    </form>
    <?php if ($number !== ''): ?>
        <?php if ($certificate): ?>
            <section class="card certificate-valid" role="status">
                <div class="card-body">
                    <span class="badge">Verified</span>
                    <h2><?= e($certificate['process_title'] ?: $certificate['title']) ?></h2>
                    <p>Issued to <strong><?= e($certificate['full_name']) ?></strong> on <?= e(date('d F Y', strtotime($certificate['issued_at']))) ?>.</p>
                    <small><?= e($certificate['certificate_number']) ?></small>
                </div>
            </section>
        <?php else: ?>
            <div class="alert alert-error">No certificate was found for that number.</div>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
