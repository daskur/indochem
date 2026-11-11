<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$processId = filter_input(INPUT_GET, 'process_id', FILTER_VALIDATE_INT) ?: null;

$processes = $pdo->query(
    'SELECT DISTINCT p.id,p.title FROM videos v
     JOIN processes p ON p.id=v.process_id
     WHERE v.status="published" AND p.status="published"
     ORDER BY p.title'
)->fetchAll();

$sql = 'SELECT v.*,p.title AS process_title,e.name AS equipment_name
        FROM videos v
        LEFT JOIN processes p ON p.id=v.process_id AND p.status="published"
        LEFT JOIN equipment e ON e.id=v.equipment_id
        WHERE v.status="published"
          AND (p.id IS NOT NULL OR v.equipment_id IS NOT NULL OR (v.process_id IS NULL AND v.equipment_id IS NULL))';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (v.title LIKE ? OR v.description LIKE ? OR p.title LIKE ? OR e.name LIKE ?)';
    $like = '%' . $query . '%';
    array_push($parameters, $like, $like, $like, $like);
}
if ($processId) {
    $sql .= ' AND v.process_id=?';
    $parameters[] = $processId;
}
$sql .= ' ORDER BY v.created_at DESC,v.title ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$videos = $statement->fetchAll();

$pageTitle = 'Video Tutorials';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Video Tutorials</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Visual learning</span>
            <h1>Video Tutorials</h1>
            <p>Watch focused industrial process and equipment explanations.</p>
        </div>
        <span class="module-count"><?= count($videos) ?> video<?= count($videos) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter video-filter" method="get" action="<?= e(url('/videos/index.php')) ?>">
        <div>
            <label for="video-search">Search tutorials</label>
            <input class="form-control" id="video-search" name="q" type="search"
                   value="<?= e($query) ?>" placeholder="Title, process or equipment">
        </div>
        <div>
            <label for="video-process">Process</label>
            <select class="form-control" id="video-process" name="process_id">
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

    <?php if ($videos): ?>
        <div class="video-grid">
            <?php foreach ($videos as $video): ?>
                <article class="card video-card">
                    <a class="video-thumbnail" href="<?= e(url('/videos/view.php?id=' . $video['id'])) ?>" aria-label="Watch <?= e($video['title']) ?>">
                        <?php if (!empty($video['thumbnail'])): ?>
                            <img src="<?= e(preg_match('#^https?://#i', $video['thumbnail']) ? $video['thumbnail'] : url('/' . ltrim($video['thumbnail'], '/'))) ?>" alt="" loading="lazy">
                        <?php else: ?>
                            <span class="video-play" aria-hidden="true">▶</span>
                        <?php endif; ?>
                        <?php if (!empty($video['duration'])): ?><small><?= e($video['duration']) ?></small><?php endif; ?>
                    </a>
                    <div class="card-body">
                        <h2><a href="<?= e(url('/videos/view.php?id=' . $video['id'])) ?>"><?= e($video['title']) ?></a></h2>
                        <p><?= e($video['description'] ?: 'Watch this industrial learning tutorial.') ?></p>
                        <?php if (!empty($video['process_title'])): ?>
                            <span class="badge"><?= e($video['process_title']) ?></span>
                        <?php elseif (!empty($video['equipment_name'])): ?>
                            <span class="badge"><?= e($video['equipment_name']) ?></span>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No video tutorials found</h2>
            <p>Try a different search term or process filter.</p>
            <a class="button button-secondary" href="<?= e(url('/videos/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
