<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));

$categories = $pdo->query(
    'SELECT DISTINCT category FROM equipment
     WHERE category IS NOT NULL AND category <> ""
     ORDER BY category ASC'
)->fetchAll();

$sql = 'SELECT id,name,category,description,function,image FROM equipment WHERE 1=1';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (name LIKE ? OR category LIKE ? OR description LIKE ? OR function LIKE ? OR working_principle LIKE ?)';
    $likeQuery = '%' . $query . '%';
    array_push($parameters, $likeQuery, $likeQuery, $likeQuery, $likeQuery, $likeQuery);
}
if ($category !== '') {
    $sql .= ' AND category = ?';
    $parameters[] = $category;
}
$sql .= ' ORDER BY name ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$equipmentItems = $statement->fetchAll();

$pageTitle = 'Equipment Library';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a>
        <span aria-hidden="true">/</span>
        <span>Equipment Library</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Industrial machinery</span>
            <h1>Equipment Library</h1>
            <p>Study machine functions, operating principles, maintenance and safety requirements.</p>
        </div>
        <span class="module-count"><?= count($equipmentItems) ?> item<?= count($equipmentItems) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter equipment-filter" method="get" action="<?= e(url('/equipment/index.php')) ?>">
        <div>
            <label for="equipment-search">Search equipment</label>
            <input class="form-control" id="equipment-search" name="q" type="search" value="<?= e($query) ?>" placeholder="Name, function or principle">
        </div>
        <div>
            <label for="equipment-category">Category</label>
            <select class="form-control" id="equipment-category" name="category">
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

    <?php if ($equipmentItems): ?>
        <div class="library-grid">
            <?php foreach ($equipmentItems as $item): ?>
                <article class="card library-card">
                    <div class="library-card-icon" aria-hidden="true"><?= e(strtoupper(substr($item['name'], 0, 2))) ?></div>
                    <div class="card-body">
                        <?php if (!empty($item['category'])): ?>
                            <span class="badge"><?= e($item['category']) ?></span>
                        <?php endif; ?>
                        <h2><a href="<?= e(url('/equipment/view.php?id=' . $item['id'])) ?>"><?= e($item['name']) ?></a></h2>
                        <p><?= e($item['function'] ?: $item['description'] ?: 'Explore this industrial equipment in detail.') ?></p>
                        <a class="text-link" href="<?= e(url('/equipment/view.php?id=' . $item['id'])) ?>">View equipment →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No equipment found</h2>
            <p>Try a different search term or category.</p>
            <a class="button button-secondary" href="<?= e(url('/equipment/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
