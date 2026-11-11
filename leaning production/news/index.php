<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$category = trim((string) ($_GET['category'] ?? ''));
$parameters = [];
$sql = 'SELECT id,title,content,image,category,published_at
        FROM news WHERE status="published"';
if ($category !== '') {
    $sql .= ' AND category=?';
    $parameters[] = $category;
}
$sql .= ' ORDER BY published_at DESC,id DESC';
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$newsItems = $statement->fetchAll();

$categoryStatement = $pdo->query(
    'SELECT DISTINCT category FROM news
     WHERE status="published" AND category IS NOT NULL AND category<>""
     ORDER BY category'
);
$categories = $categoryStatement->fetchAll();

$pageTitle = 'Industrial News';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page news-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Industrial News</span>
    </nav>
    <header class="module-heading">
        <div>
            <span class="section-kicker">Industry updates</span>
            <h1>Industrial News</h1>
            <p>Educational updates from the industrial production and learning community.</p>
        </div>
        <span class="module-count"><?= count($newsItems) ?> article<?= count($newsItems) === 1 ? '' : 's' ?></span>
    </header>
    <form class="process-filter news-filter" method="get" action="<?= e(url('/news/index.php')) ?>">
        <div>
            <label for="news-category">Category</label>
            <select class="form-control" id="news-category" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $row): ?>
                    <option value="<?= e($row['category']) ?>" <?= $category === $row['category'] ? 'selected' : '' ?>><?= e($row['category']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Filter</button>
    </form>
    <?php if ($newsItems): ?>
        <div class="news-listing-grid">
            <?php foreach ($newsItems as $item): ?>
                <article class="card news-listing-card">
                    <?php if (!empty($item['image'])): ?>
                        <img src="<?= e(url('/' . ltrim($item['image'],'/'))) ?>" alt="">
                    <?php endif; ?>
                    <div class="card-body">
                        <?php if (!empty($item['category'])): ?><span class="badge"><?= e($item['category']) ?></span><?php endif; ?>
                        <h2><a href="<?= e(url('/news/view.php?id=' . $item['id'])) ?>"><?= e($item['title']) ?></a></h2>
                        <small><?= e($item['published_at'] ? date('d F Y', strtotime($item['published_at'])) : '') ?></small>
                        <p><?= e(substr(strip_tags($item['content']),0,180)) ?><?= strlen(strip_tags($item['content'])) > 180 ? '...' : '' ?></p>
                        <a class="text-link" href="<?= e(url('/news/view.php?id=' . $item['id'])) ?>">Read article</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><h2>No news articles found</h2><p>Try another category or check back later.</p></div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
