<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$processId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$processId) {
    http_response_code(404);
    exit('Process not found.');
}

$statement = $pdo->prepare('SELECT * FROM processes WHERE id = ? AND status = "published" LIMIT 1');
$statement->execute([$processId]);
$process = $statement->fetch();
if (!$process) {
    http_response_code(404);
    exit('Process not found.');
}

function processRows(PDO $pdo, string $sql, int $processId): array
{
    $statement = $pdo->prepare($sql);
    $statement->execute([$processId]);
    return $statement->fetchAll();
}

$materials = processRows($pdo, 'SELECT * FROM process_materials WHERE process_id = ? ORDER BY id', $processId);
$equipment = processRows($pdo,
    'SELECT e.*, pe.purpose FROM process_equipment pe JOIN equipment e ON e.id = pe.equipment_id WHERE pe.process_id = ? ORDER BY e.name',
    $processId);
$steps = processRows($pdo, 'SELECT * FROM process_steps WHERE process_id = ? ORDER BY step_number ASC', $processId);
$reactions = processRows($pdo, 'SELECT * FROM process_reactions WHERE process_id = ? ORDER BY id', $processId);
$safety = processRows($pdo, 'SELECT * FROM process_safety WHERE process_id = ? ORDER BY id', $processId);
$quality = processRows($pdo, 'SELECT * FROM process_quality_control WHERE process_id = ? ORDER BY id', $processId);
$applications = processRows($pdo, 'SELECT * FROM process_applications WHERE process_id = ? ORDER BY id', $processId);
$endProducts = processRows($pdo, 'SELECT * FROM process_end_products WHERE process_id = ? ORDER BY id', $processId);
$flowDiagrams = processRows($pdo, 'SELECT * FROM process_flow_diagrams WHERE process_id = ? ORDER BY id', $processId);
$videos = processRows($pdo, 'SELECT * FROM videos WHERE process_id = ? AND status = "published" ORDER BY id DESC', $processId);
$quizzes = processRows($pdo, 'SELECT * FROM quizzes WHERE process_id = ? AND status = "published" ORDER BY id DESC', $processId);

$progress = null;
if (is_logged_in()) {
    $statement = $pdo->prepare(
        'SELECT last_step,progress_percentage,completed FROM user_progress WHERE user_id = ? AND process_id = ? LIMIT 1'
    );
    $statement->execute([(int) current_user()['id'], $processId]);
    $progress = $statement->fetch();
}

$pageTitle = $process['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container process-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/processes/index.php')) ?>">Processes</a><span aria-hidden="true">/</span>
        <span><?= e($process['title']) ?></span>
    </nav>

    <header class="process-header">
        <div>
            <?php if (!empty($process['category'])): ?><span class="badge"><?= e($process['category']) ?></span><?php endif; ?>
            <h1><?= e($process['title']) ?></h1>
            <p><?= e($process['short_description'] ?: $process['description']) ?></p>
        </div>
        <div class="process-header-actions">
            <?php $bookmarkType='process'; $bookmarkId=$processId; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
        </div>
        <?php if (is_logged_in()): ?>
            <div class="progress-summary card">
                <div class="card-body">
                    <strong>Learning progress</strong>
                    <div class="progress-track" aria-label="<?= e((string) ($progress['progress_percentage'] ?? 0)) ?> percent complete">
                        <span style="width:<?= e((string) ($progress['progress_percentage'] ?? 0)) ?>%"></span>
                    </div>
                    <?php if ((int) ($progress['completed'] ?? 0) === 1): ?>
                        <a class="button button-primary" href="<?= e(url('/certificates/index.php')) ?>">View certificate</a>
                    <?php endif; ?>
                    <small><?= e((string) ($progress['progress_percentage'] ?? 0)) ?>% complete</small>
                </div>
            </div>
        <?php else: ?>
            <a class="button button-primary" href="<?= e(url('/login.php')) ?>">Sign in to track progress</a>
        <?php endif; ?>
    </header>

    <div class="process-layout">
        <?php require dirname(__DIR__) . '/includes/sidebar.php'; ?>
        <div class="process-content">
            <section class="process-section" id="overview">
                <h2>Overview</h2>
                <p><?= nl2br(e($process['description'])) ?></p>
            </section>

            <section class="process-section" id="raw-materials">
                <h2>Raw Materials</h2>
                <?php if ($materials): ?>
                    <div class="info-grid">
                        <?php foreach ($materials as $material): ?>
                            <article class="card info-card">
                                <div class="card-body">
                                    <h3><?= e($material['material_name']) ?></h3>
                                    <p><?= e($material['description']) ?></p>
                                    <?php if (!empty($material['quantity'])): ?>
                                        <small><?= e((string) $material['quantity']) ?> <?= e($material['unit']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?><p class="not-provided">Raw materials have not been added yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="equipment">
                <h2>Equipment</h2>
                <?php if ($equipment): ?>
                    <div class="info-grid">
                        <?php foreach ($equipment as $item): ?>
                            <article class="card info-card"><div class="card-body">
                                <h3><?= e($item['name']) ?></h3>
                                <p><?= e($item['purpose'] ?: $item['description']) ?></p>
                            </div></article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?><p class="not-provided">Equipment has not been added yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="process-steps">
                <h2>Step-by-Step Production</h2>
                <?php if ($steps): ?>
                    <div class="step-list">
                        <?php foreach ($steps as $step): ?>
                            <?php
                            $stepComplete = is_logged_in()
                                && (int) ($progress['last_step'] ?? 0) >= (int) $step['step_number'];
                            ?>
                            <article class="step-card <?= $stepComplete ? 'is-complete' : '' ?>">
                                <div class="step-number"><?= e((string) $step['step_number']) ?></div>
                                <div class="step-content">
                                    <h3><a href="<?= e(url('/processes/step.php?id=' . $step['id'])) ?>"><?= e($step['title']) ?></a></h3>
                                    <p><?= nl2br(e($step['description'])) ?></p>
                                    <div class="step-meta">
                                        <?php foreach (['temperature','pressure','duration'] as $field): ?>
                                            <?php if (!empty($step[$field])): ?>
                                                <span><strong><?= e(ucfirst($field)) ?>:</strong> <?= e($step[$field]) ?></span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php if (is_logged_in()): ?>
                                        <?php if (!$stepComplete): ?>
                                            <form method="post" action="<?= e(url('/processes/step.php')) ?>">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="process_id" value="<?= e((string) $processId) ?>">
                                                <input type="hidden" name="step_number" value="<?= e((string) $step['step_number']) ?>">
                                                <button class="button button-secondary button-small" type="submit">Mark step complete</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge">Completed</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?><p class="not-provided">Production steps have not been added yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="reactions"><h2>Chemical Reactions</h2>
                <?php if ($reactions): ?>
                    <?php foreach ($reactions as $reaction): ?>
                        <article class="reaction-card card"><div class="card-body">
                            <h3><?= e($reaction['reaction_title']) ?></h3>
                            <?php if (!empty($reaction['equation'])): ?><code><?= e($reaction['equation']) ?></code><?php endif; ?>
                            <p><?= e($reaction['description']) ?></p>
                        </div></article>
                    <?php endforeach; ?>
                <?php else: ?><p class="not-provided">Chemical reactions have not been added yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="flow-diagram"><h2>Flow Diagram</h2>
                <?php if ($flowDiagrams): ?>
                    <?php foreach ($flowDiagrams as $diagram): ?>
                        <img src="<?= e(url('/uploads/processes/' . $diagram['diagram_image'])) ?>" alt="<?= e($diagram['title']) ?>">
                    <?php endforeach; ?>
                <?php else: ?><p class="not-provided">No flow diagram is available yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="quality-control"><h2>Quality Control</h2>
                <?php if ($quality): ?>
                    <div class="table-wrap"><table class="data-table"><thead><tr><th>Parameter</th><th>Acceptable range</th><th>Testing method</th></tr></thead><tbody>
                    <?php foreach ($quality as $control): ?><tr>
                        <td><?= e($control['parameter'] ?: $control['title']) ?></td>
                        <td><?= e($control['acceptable_range']) ?></td>
                        <td><?= e($control['testing_method']) ?></td>
                    </tr><?php endforeach; ?></tbody></table></div>
                <?php else: ?><p class="not-provided">Quality controls have not been added yet.</p><?php endif; ?>
            </section>

            <section class="process-section" id="safety"><h2>Safety Precautions</h2>
                <?php if ($safety): ?>
                    <?php foreach ($safety as $item): ?>
                        <article class="safety-card"><span class="badge"><?= e(strtoupper($item['hazard_level'])) ?></span><div><h3><?= e($item['title']) ?></h3><p><?= e($item['description']) ?></p></div></article>
                    <?php endforeach; ?>
                <?php else: ?><p class="not-provided">No process-specific safety notes have been added.</p><?php endif; ?>
            </section>

            <section class="process-section" id="applications"><h2>Applications and End Products</h2>
                <?php if ($applications || $endProducts): ?>
                    <div class="info-grid">
                        <?php foreach ($applications as $application): ?><article class="card info-card"><div class="card-body"><span class="badge">Application</span><h3><?= e($application['title']) ?></h3><p><?= e($application['description']) ?></p></div></article><?php endforeach; ?>
                        <?php foreach ($endProducts as $product): ?><article class="card info-card"><div class="card-body"><span class="badge">End Product</span><h3><?= e($product['name']) ?></h3><p><?= e($product['description']) ?></p></div></article><?php endforeach; ?>
                    </div>
                <?php else: ?><p class="not-provided">Applications and end products have not been added yet.</p><?php endif; ?>
            </section>

            <?php if ($videos || $quizzes): ?>
                <section class="process-section"><h2>Related Learning</h2>
                    <?php foreach ($videos as $video): ?><p><a href="<?= e(url('/videos/view.php?id=' . $video['id'])) ?>"><?= e($video['title']) ?></a></p><?php endforeach; ?>
                    <?php foreach ($quizzes as $quiz): ?><p><a href="<?= e(url('/quizzes/start.php?id=' . $quiz['id'])) ?>"><?= e($quiz['title']) ?></a></p><?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
