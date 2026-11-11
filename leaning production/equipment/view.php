<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$equipmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$equipmentId) {
    http_response_code(404);
    exit('Equipment not found.');
}

$statement = $pdo->prepare('SELECT * FROM equipment WHERE id = ? LIMIT 1');
$statement->execute([$equipmentId]);
$equipment = $statement->fetch();
if (!$equipment) {
    http_response_code(404);
    exit('Equipment not found.');
}

$processStatement = $pdo->prepare(
    'SELECT p.id,p.title,p.slug,p.category,pe.purpose
     FROM process_equipment pe
     JOIN processes p ON p.id = pe.process_id
     WHERE pe.equipment_id = ? AND p.status = "published"
     ORDER BY p.title ASC'
);
$processStatement->execute([$equipmentId]);
$processes = $processStatement->fetchAll();

$videoStatement = $pdo->prepare(
    'SELECT id,title,description,video_url,thumbnail,duration
     FROM videos
     WHERE equipment_id = ? AND status = "published"
     ORDER BY id DESC'
);
$videoStatement->execute([$equipmentId]);
$videos = $videoStatement->fetchAll();

$pageTitle = $equipment['name'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container equipment-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/equipment/index.php')) ?>">Equipment Library</a><span aria-hidden="true">/</span>
        <span><?= e($equipment['name']) ?></span>
    </nav>

    <header class="equipment-header">
        <div class="equipment-hero-icon" aria-hidden="true"><?= e(strtoupper(substr($equipment['name'], 0, 2))) ?></div>
        <div>
            <?php if (!empty($equipment['category'])): ?>
                <span class="badge"><?= e($equipment['category']) ?></span>
            <?php endif; ?>
            <h1><?= e($equipment['name']) ?></h1>
            <p><?= e($equipment['description'] ?: 'Learn about this item in the IPLA industrial equipment library.') ?></p>
        </div>
        <div class="detail-bookmark-action">
            <?php $bookmarkType='equipment'; $bookmarkId=(int)$equipment['id']; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
        </div>
    </header>

    <div class="equipment-layout">
        <aside class="equipment-sidebar" aria-label="Equipment sections">
            <nav>
                <a href="#function">Function</a>
                <a href="#working-principle">Working Principle</a>
                <a href="#main-parts">Main Parts</a>
                <a href="#maintenance">Maintenance</a>
                <a href="#safety">Safety Precautions</a>
                <a href="#processes">Related Processes</a>
                <?php if ($videos): ?><a href="#videos">Videos</a><?php endif; ?>
            </nav>
        </aside>

        <div class="equipment-content">
            <section class="process-section" id="function">
                <h2>Function</h2>
                <?php if (!empty($equipment['function'])): ?>
                    <p><?= nl2br(e($equipment['function'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Function information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="working-principle">
                <h2>Working Principle</h2>
                <?php if (!empty($equipment['working_principle'])): ?>
                    <p><?= nl2br(e($equipment['working_principle'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Working principle information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="main-parts">
                <h2>Main Parts</h2>
                <?php if (!empty($equipment['main_parts'])): ?>
                    <p><?= nl2br(e($equipment['main_parts'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Main-part information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="maintenance">
                <h2>Maintenance</h2>
                <?php if (!empty($equipment['maintenance'])): ?>
                    <p><?= nl2br(e($equipment['maintenance'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Maintenance information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="safety">
                <h2>Safety Precautions</h2>
                <?php if (!empty($equipment['safety_precautions'])): ?>
                    <div class="safety-card">
                        <span class="badge">SAFETY</span>
                        <p><?= nl2br(e($equipment['safety_precautions'])) ?></p>
                    </div>
                <?php else: ?>
                    <p class="not-provided">Safety precautions have not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="processes">
                <h2>Related Industrial Processes</h2>
                <?php if ($processes): ?>
                    <div class="info-grid">
                        <?php foreach ($processes as $process): ?>
                            <article class="card info-card">
                                <div class="card-body">
                                    <?php if (!empty($process['category'])): ?>
                                        <span class="badge"><?= e($process['category']) ?></span>
                                    <?php endif; ?>
                                    <h3><a href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>"><?= e($process['title']) ?></a></h3>
                                    <?php if (!empty($process['purpose'])): ?>
                                        <p><?= nl2br(e($process['purpose'])) ?></p>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="not-provided">No published industrial processes are linked to this equipment.</p>
                <?php endif; ?>
            </section>

            <?php if ($videos): ?>
                <section class="process-section" id="videos">
                    <h2>Related Videos</h2>
                    <?php foreach ($videos as $video): ?>
                        <article class="card info-card">
                            <div class="card-body">
                                <h3><a href="<?= e(url('/videos/view.php?id=' . $video['id'])) ?>"><?= e($video['title']) ?></a></h3>
                                <?php if (!empty($video['duration'])): ?><small><?= e($video['duration']) ?></small><?php endif; ?>
                                <?php if (!empty($video['description'])): ?><p><?= e($video['description']) ?></p><?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
