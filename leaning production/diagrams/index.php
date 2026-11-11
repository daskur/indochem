<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$processId = filter_input(INPUT_GET, 'process_id', FILTER_VALIDATE_INT) ?: null;

$processes = $pdo->query(
    'SELECT DISTINCT p.id,p.title FROM process_flow_diagrams d
     JOIN processes p ON p.id=d.process_id
     WHERE p.status="published" ORDER BY p.title'
)->fetchAll();

$sql = 'SELECT d.*,p.title AS process_title,p.category
        FROM process_flow_diagrams d
        JOIN processes p ON p.id=d.process_id
        WHERE p.status="published"';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (d.title LIKE ? OR d.description LIKE ? OR p.title LIKE ?)';
    $like = '%' . $query . '%';
    array_push($parameters, $like, $like, $like);
}
if ($processId) {
    $sql .= ' AND d.process_id=?';
    $parameters[] = $processId;
}
$sql .= ' ORDER BY p.title ASC,d.title ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$diagrams = $statement->fetchAll();

$pageTitle = 'Flow Diagrams';
require dirname(__DIR__) . '/includes/header.php';

function diagramImageUrl(string $path): string
{
    if (preg_match('#^https?://#i', $path)) {
        return $path;
    }
    return url('/' . ltrim($path, '/'));
}
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Flow Diagrams</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Process visualization</span>
            <h1>Flow Diagrams</h1>
            <p>Study how materials, equipment and operating stages connect in industrial production.</p>
        </div>
        <span class="module-count"><?= count($diagrams) ?> diagram<?= count($diagrams) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter diagram-filter" method="get" action="<?= e(url('/diagrams/index.php')) ?>">
        <div>
            <label for="diagram-search">Search diagrams</label>
            <input class="form-control" id="diagram-search" name="q" type="search"
                   value="<?= e($query) ?>" placeholder="Title, process or description">
        </div>
        <div>
            <label for="diagram-process">Process</label>
            <select class="form-control" id="diagram-process" name="process_id">
                <option value="">All processes</option>
                <?php foreach ($processes as $process): ?>
                    <option value="<?= e((string) $process['id']) ?>" <?= $processId === (int) $process['id'] ? 'selected' : '' ?>>
                        <?= e($process['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Filter</button>
    </form>

    <?php if ($diagrams): ?>
        <div class="diagram-grid">
            <?php foreach ($diagrams as $diagram): ?>
                <article class="card diagram-card">
                    <a class="diagram-preview" href="<?= e(url('/diagrams/view.php?id=' . $diagram['id'])) ?>" aria-label="View <?= e($diagram['title']) ?>">
                        <img src="<?= e(diagramImageUrl($diagram['diagram_image'])) ?>" alt="<?= e($diagram['title']) ?>" loading="lazy">
                    </a>
                    <div class="card-body">
                        <span class="badge"><?= e($diagram['category']) ?></span>
                        <h2><a href="<?= e(url('/diagrams/view.php?id=' . $diagram['id'])) ?>"><?= e($diagram['title']) ?></a></h2>
                        <p><?= e($diagram['description'] ?: 'Explore the process stages and material flow.') ?></p>
                        <small><?= e($diagram['process_title']) ?></small>
                        <a class="text-link" href="<?= e(url('/diagrams/view.php?id=' . $diagram['id'])) ?>">Open diagram →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No flow diagrams found</h2>
            <p>Try a different search term or process filter.</p>
            <a class="button button-secondary" href="<?= e(url('/diagrams/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
