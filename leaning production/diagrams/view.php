<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$diagramId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$diagramId) {
    http_response_code(404);
    exit('Flow diagram not found.');
}

$statement = $pdo->prepare(
    'SELECT d.*,p.title AS process_title,p.category,p.description AS process_description
     FROM process_flow_diagrams d
     JOIN processes p ON p.id=d.process_id
     WHERE d.id=? AND p.status="published" LIMIT 1'
);
$statement->execute([$diagramId]);
$diagram = $statement->fetch();
if (!$diagram) {
    http_response_code(404);
    exit('Flow diagram not found.');
}

$imageUrl = preg_match('#^https?://#i', $diagram['diagram_image'])
    ? $diagram['diagram_image']
    : url('/' . ltrim($diagram['diagram_image'], '/'));

$relatedStatement = $pdo->prepare(
    'SELECT d.id,d.title,d.diagram_image
     FROM process_flow_diagrams d
     JOIN processes p ON p.id=d.process_id
     WHERE d.process_id=? AND d.id<>? AND p.status="published"
     ORDER BY d.title LIMIT 6'
);
$relatedStatement->execute([(int) $diagram['process_id'], $diagramId]);
$related = $relatedStatement->fetchAll();

$pageTitle = $diagram['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container diagram-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/diagrams/index.php')) ?>">Flow Diagrams</a><span aria-hidden="true">/</span>
        <span><?= e($diagram['title']) ?></span>
    </nav>

    <header class="diagram-header">
        <div>
            <span class="badge"><?= e($diagram['category']) ?></span>
            <h1><?= e($diagram['title']) ?></h1>
            <p><?= e($diagram['description'] ?: 'Visual overview of the ' . $diagram['process_title'] . ' process.') ?></p>
        </div>
        <a class="button button-secondary" href="<?= e(url('/processes/view.php?id=' . $diagram['process_id'])) ?>">View process</a>
        <?php $bookmarkType='diagram'; $bookmarkId=$diagramId; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
    </header>

    <figure class="diagram-viewer">
        <img src="<?= e($imageUrl) ?>" alt="<?= e($diagram['title']) ?>">
        <figcaption><?= e($diagram['process_title']) ?> — Industrial process flow</figcaption>
    </figure>

    <section class="process-section">
        <h2>About this flow</h2>
        <p><?= nl2br(e($diagram['description'] ?: $diagram['process_description'])) ?></p>
        <p><strong>Process:</strong>
            <a href="<?= e(url('/processes/view.php?id=' . $diagram['process_id'])) ?>"><?= e($diagram['process_title']) ?></a>
        </p>
    </section>

    <?php if ($related): ?>
        <section class="process-section">
            <h2>More diagrams for <?= e($diagram['process_title']) ?></h2>
            <div class="diagram-related-grid">
                <?php foreach ($related as $item): ?>
                    <?php
                    $relatedUrl = preg_match('#^https?://#i', $item['diagram_image'])
                        ? $item['diagram_image']
                        : url('/' . ltrim($item['diagram_image'], '/'));
                    ?>
                    <article class="card diagram-related-card">
                        <img src="<?= e($relatedUrl) ?>" alt="" loading="lazy">
                        <div class="card-body">
                            <h3><a href="<?= e(url('/diagrams/view.php?id=' . $item['id'])) ?>"><?= e($item['title']) ?></a></h3>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
