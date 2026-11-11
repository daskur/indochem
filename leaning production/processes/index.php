<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$categories = $pdo->query(
    'SELECT DISTINCT category FROM processes WHERE status = "published" AND category IS NOT NULL AND category <> "" ORDER BY category'
)->fetchAll();

$sql = 'SELECT id,title,slug,category,short_description,featured_image
        FROM processes WHERE status = "published"';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (title LIKE ? OR category LIKE ? OR short_description LIKE ? OR description LIKE ?)';
    $likeQuery = '%' . $query . '%';
    array_push($parameters, $likeQuery, $likeQuery, $likeQuery, $likeQuery);
}
if ($category !== '') {
    $sql .= ' AND category = ?';
    $parameters[] = $category;
}
$sql .= ' ORDER BY title ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$processes = $statement->fetchAll();

$pageTitle = 'Industrial Processes';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container process-listing">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a>
        <span aria-hidden="true">/</span>
        <span>Industrial Processes</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Production learning library</span>
            <h1>Industrial Processes</h1>
            <p>Explore manufacturing processes from raw materials to finished products.</p>
        </div>
        <span class="module-count"><?= count($processes) ?> process<?= count($processes) === 1 ? '' : 'es' ?></span>
    </header>

    <form class="process-filter" method="get" action="<?= e(url('/processes/index.php')) ?>">
        <div>
            <label for="process-search">Search</label>
            <input class="form-control" id="process-search" name="q" type="search" value="<?= e($query) ?>" placeholder="Search process or topic">
        </div>
        <div>
            <label for="process-category">Category</label>
            <select class="form-control" id="process-category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= e($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>>
                        <?= e($row['category']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Filter</button>
    </form>

    <?php if ($processes): ?>
        <div class="process-card-grid listing-grid">
            <?php foreach ($processes as $process): ?>
                <article class="card process-card">
                    <div class="process-card-visual" aria-hidden="true"><span><?= e(substr($process['title'], 0, 1)) ?></span></div>
                    <div class="card-body">
                        <?php if (!empty($process['category'])): ?>
                            <span class="badge"><?= e($process['category']) ?></span>
                        <?php endif; ?>
                        <h2><a href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>"><?= e($process['title']) ?></a></h2>
                        <p><?= e($process['short_description'] ?: 'Explore this production process step by step.') ?></p>
                        <a class="text-link" href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>">Open process →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No processes found</h2>
            <p>Try a different search term or category.</p>
            <a class="button button-secondary" href="<?= e(url('/processes/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
