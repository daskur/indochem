<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';
require_once dirname(__DIR__) . '/config/certificates.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    if (!verify_csrf()) {
        http_response_code(400);
        exit('Invalid request.');
    }

    $processId = filter_input(INPUT_POST, 'process_id', FILTER_VALIDATE_INT);
    $stepNumber = filter_input(INPUT_POST, 'step_number', FILTER_VALIDATE_INT);
    if (!$processId || !$stepNumber || $stepNumber < 1) {
        http_response_code(400);
        exit('Invalid step.');
    }

    $processStatement = $pdo->prepare('SELECT id FROM processes WHERE id = ? AND status = "published" LIMIT 1');
    $processStatement->execute([$processId]);
    if (!$processStatement->fetch()) {
        http_response_code(404);
        exit('Process not found.');
    }

    $stepStatement = $pdo->prepare(
        'SELECT MAX(step_number) AS max_step FROM process_steps WHERE process_id = ?'
    );
    $stepStatement->execute([$processId]);
    $maxStep = (int) ($stepStatement->fetch()['max_step'] ?? 0);
    $validStep = $pdo->prepare(
        'SELECT id FROM process_steps WHERE process_id = ? AND step_number = ? LIMIT 1'
    );
    $validStep->execute([$processId, $stepNumber]);
    if (!$validStep->fetch()) {
        http_response_code(404);
        exit('Step not found.');
    }

    $progressStatement = $pdo->prepare(
        'SELECT last_step FROM user_progress WHERE user_id = ? AND process_id = ? LIMIT 1'
    );
    $progressStatement->execute([(int) current_user()['id'], $processId]);
    $current = $progressStatement->fetch();
    $lastStep = max((int) ($current['last_step'] ?? 0), $stepNumber);
    $percentage = $maxStep > 0 ? round(($lastStep / $maxStep) * 100, 2) : 0.00;
    $completed = $maxStep > 0 && $lastStep >= $maxStep;

    $upsert = $pdo->prepare(
        'INSERT INTO user_progress
            (user_id,process_id,last_step,progress_percentage,completed,completed_at)
         VALUES (?, ?, ?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE
            last_step = VALUES(last_step),
            progress_percentage = VALUES(progress_percentage),
            completed = VALUES(completed),
            completed_at = VALUES(completed_at)'
    );
    $upsert->execute([
        (int) current_user()['id'],
        $processId,
        $lastStep,
        $percentage,
        $completed ? 1 : 0,
        $completed ? date('Y-m-d H:i:s') : null,
    ]);

    if ($completed) {
        $processName = $pdo->prepare('SELECT title FROM processes WHERE id=? LIMIT 1');
        $processName->execute([$processId]);
        $processRow = $processName->fetch();
        if ($processRow) {
            issue_process_certificate(
                $pdo,
                (int) current_user()['id'],
                $processId,
                (string) $processRow['title']
            );
        }
    }

    redirect('/processes/view.php?id=' . $processId);
}

$stepId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$stepId) {
    http_response_code(404);
    exit('Step not found.');
}

$statement = $pdo->prepare(
    'SELECT s.*,p.title AS process_title,p.status
     FROM process_steps s JOIN processes p ON p.id = s.process_id
     WHERE s.id = ? AND p.status = "published" LIMIT 1'
);
$statement->execute([$stepId]);
$step = $statement->fetch();
if (!$step) {
    http_response_code(404);
    exit('Step not found.');
}

$pageTitle = $step['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container process-step-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span>/</span>
        <a href="<?= e(url('/processes/view.php?id=' . $step['process_id'])) ?>"><?= e($step['process_title']) ?></a><span>/</span>
        <span>Step <?= e((string) $step['step_number']) ?></span>
    </nav>
    <article class="step-detail card">
        <div class="card-body">
            <span class="badge">Step <?= e((string) $step['step_number']) ?></span>
            <h1><?= e($step['title']) ?></h1>
            <p><?= nl2br(e($step['description'])) ?></p>
            <div class="step-detail-grid">
                <?php foreach (['temperature','pressure','duration','input_material','output_material','equipment_used'] as $field): ?>
                    <?php if (!empty($step[$field])): ?>
                        <div><strong><?= e(str_replace('_', ' ', ucfirst($field))) ?></strong><p><?= nl2br(e($step[$field])) ?></p></div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <?php if (!empty($step['safety_note'])): ?><section><h2>Safety Note</h2><p><?= nl2br(e($step['safety_note'])) ?></p></section><?php endif; ?>
            <?php if (!empty($step['quality_note'])): ?><section><h2>Quality Note</h2><p><?= nl2br(e($step['quality_note'])) ?></p></section><?php endif; ?>
            <a class="button button-primary" href="<?= e(url('/processes/view.php?id=' . $step['process_id'])) ?>">Back to process</a>
        </div>
    </article>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
