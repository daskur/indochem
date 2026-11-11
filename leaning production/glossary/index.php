<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$category = trim((string) ($_GET['category'] ?? ''));
$letter = strtoupper(trim((string) ($_GET['letter'] ?? '')));
$letter = strlen($letter) === 1 && ctype_alpha($letter) ? $letter : '';

$categories = $pdo->query(
    'SELECT DISTINCT category FROM glossary
     WHERE category IS NOT NULL AND category <> ""
     ORDER BY category ASC'
)->fetchAll();

$sql = 'SELECT * FROM glossary WHERE 1=1';
$parameters = [];
if ($query !== '') {
    $sql .= ' AND (term LIKE ? OR definition LIKE ? OR category LIKE ?)';
    $like = '%' . $query . '%';
    array_push($parameters, $like, $like, $like);
}
if ($category !== '') {
    $sql .= ' AND category=?';
    $parameters[] = $category;
}
if ($letter !== '') {
    $sql .= ' AND term LIKE ?';
    $parameters[] = $letter . '%';
}
$sql .= ' ORDER BY term ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$terms = $statement->fetchAll();

$pageTitle = 'Industrial Glossary';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Glossary</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Technical reference</span>
            <h1>Industrial Glossary</h1>
            <p>Find clear definitions for common industrial chemistry and process-engineering terms.</p>
        </div>
        <span class="module-count"><?= count($terms) ?> term<?= count($terms) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter glossary-filter" method="get" action="<?= e(url('/glossary/index.php')) ?>">
        <div>
            <label for="glossary-search">Search terms</label>
            <input class="form-control" id="glossary-search" name="q" type="search"
                   value="<?= e($query) ?>" placeholder="Term or definition">
        </div>
        <div>
            <label for="glossary-category">Category</label>
            <select class="form-control" id="glossary-category" name="category">
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

    <nav class="alphabet-filter" aria-label="Glossary letters">
        <a class="<?= $letter === '' ? 'active' : '' ?>" href="<?= e(url('/glossary/index.php' . ($category !== '' ? '?category=' . urlencode($category) : '') . ($query !== '' ? ($category !== '' ? '&' : '?') . 'q=' . urlencode($query) : ''))) ?>">All</a>
        <?php foreach (range('A','Z') as $alphabetLetter): ?>
            <?php
            $letterParams = [];
            if ($query !== '') $letterParams['q'] = $query;
            if ($category !== '') $letterParams['category'] = $category;
            $letterParams['letter'] = $alphabetLetter;
            ?>
            <a class="<?= $letter === $alphabetLetter ? 'active' : '' ?>" href="<?= e(url('/glossary/index.php?' . http_build_query($letterParams))) ?>"><?= $alphabetLetter ?></a>
        <?php endforeach; ?>
    </nav>

    <?php if ($terms): ?>
        <div class="glossary-list">
            <?php foreach ($terms as $term): ?>
                <article class="card glossary-card" id="<?= e(slugify($term['term'])) ?>">
                    <div class="card-body">
                        <div class="glossary-term-heading">
                            <h2><?= e($term['term']) ?></h2>
                            <?php if (!empty($term['category'])): ?><span class="badge"><?= e($term['category']) ?></span><?php endif; ?>
                        </div>
                        <p><?= nl2br(e($term['definition'])) ?></p>
                        <?php $bookmarkType='glossary'; $bookmarkId=(int)$term['id']; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No terms found</h2>
            <p>Try another search, category or alphabet letter.</p>
            <a class="button button-secondary" href="<?= e(url('/glossary/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
