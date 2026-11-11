<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$newsId = filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT);
if (!$newsId) {
    http_response_code(404);
    exit('News article not found.');
}
$statement = $pdo->prepare(
    'SELECT n.*,u.full_name AS author_name
     FROM news n LEFT JOIN users u ON u.id=n.author_id
     WHERE n.id=? AND n.status="published" LIMIT 1'
);
$statement->execute([$newsId]);
$article = $statement->fetch();
if (!$article) {
    http_response_code(404);
    exit('News article not found.');
}

$pageTitle = $article['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container news-detail-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/news/index.php')) ?>">Industrial News</a><span aria-hidden="true">/</span>
        <span><?= e($article['title']) ?></span>
    </nav>
    <article class="card news-detail-card">
        <div class="card-body">
            <?php if (!empty($article['category'])): ?><span class="badge"><?= e($article['category']) ?></span><?php endif; ?>
            <h1><?= e($article['title']) ?></h1>
            <small><?= e($article['published_at'] ? date('d F Y', strtotime($article['published_at'])) : '') ?><?= $article['author_name'] ? ' · ' . e($article['author_name']) : '' ?></small>
            <?php if (!empty($article['image'])): ?>
                <img class="news-detail-image" src="<?= e(url('/' . ltrim($article['image'],'/'))) ?>" alt="">
            <?php endif; ?>
            <div class="news-content"><?= nl2br(e($article['content'])) ?></div>
        </div>
    </article>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
