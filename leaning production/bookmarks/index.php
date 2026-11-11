<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$userId = (int) current_user()['id'];
$typeFilter = trim((string) ($_GET['type'] ?? ''));
$allowedFilters = ['process','equipment','chemical','diagram','video','quiz','glossary'];
if (!in_array($typeFilter,$allowedFilters,true)) {
    $typeFilter = '';
}

$sql = 'SELECT * FROM bookmarks WHERE user_id=?';
$parameters = [$userId];
if ($typeFilter !== '') {
    $sql .= ' AND content_type=?';
    $parameters[] = $typeFilter;
}
$sql .= ' ORDER BY created_at DESC,id DESC';
$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$bookmarks = $statement->fetchAll();

$items = [];
foreach ($bookmarks as $bookmark) {
    $type = $bookmark['content_type'];
    $id = (int) $bookmark['content_id'];
    $item = null;
    $url = '';

    if ($type === 'process') {
        $query = $pdo->prepare('SELECT id,title,category,short_description FROM processes WHERE id=? AND status="published" LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/processes/view.php?id=' . $id;
    } elseif ($type === 'equipment') {
        $query = $pdo->prepare('SELECT id,name AS title,category,description FROM equipment WHERE id=? LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/equipment/view.php?id=' . $id;
    } elseif ($type === 'chemical') {
        $query = $pdo->prepare('SELECT id,name AS title,physical_state AS category,uses AS short_description FROM chemicals WHERE id=? LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/chemicals/view.php?id=' . $id;
    } elseif ($type === 'diagram') {
        $query = $pdo->prepare('SELECT id,title,description FROM process_flow_diagrams WHERE id=? LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/diagrams/view.php?id=' . $id;
        if ($item) $item['category'] = 'Flow Diagram';
    } elseif ($type === 'video') {
        $query = $pdo->prepare('SELECT id,title,description FROM videos WHERE id=? AND status="published" LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/videos/view.php?id=' . $id;
        if ($item) $item['category'] = 'Video Tutorial';
    } elseif ($type === 'quiz') {
        $query = $pdo->prepare('SELECT id,title,description FROM quizzes WHERE id=? AND status="published" LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/quizzes/start.php?id=' . $id;
        if ($item) $item['category'] = 'Quiz';
    } elseif ($type === 'glossary') {
        $query = $pdo->prepare('SELECT id,term AS title,category,definition AS description FROM glossary WHERE id=? LIMIT 1');
        $query->execute([$id]); $item = $query->fetch(); $url = '/glossary/index.php?q=' . urlencode((string) ($item['title'] ?? ''));
    }

    if ($item) {
        $item['type'] = $type;
        $item['url'] = $url;
        $item['bookmark_id'] = (int) $bookmark['id'];
        $items[] = $item;
    }
}

$pageTitle = 'My Bookmarks';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>My Bookmarks</span>
    </nav>

    <header class="module-heading">
        <div><span class="section-kicker">Saved learning</span><h1>My Bookmarks</h1><p>Return quickly to important processes, references and practice resources.</p></div>
        <span class="module-count"><?= count($items) ?> bookmark<?= count($items) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter bookmark-filter" method="get" action="<?= e(url('/bookmarks/index.php')) ?>">
        <div>
            <label for="bookmark-type">Content type</label>
            <select class="form-control" id="bookmark-type" name="type">
                <option value="">All bookmarks</option>
                <?php foreach ($allowedFilters as $type): ?>
                    <option value="<?= e($type) ?>" <?= $typeFilter === $type ? 'selected' : '' ?>><?= e(ucfirst($type)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Filter</button>
    </form>

    <?php if ($items): ?>
        <div class="bookmark-grid">
            <?php foreach ($items as $item): ?>
                <article class="card bookmark-card">
                    <div class="card-body">
                        <span class="badge"><?= e($item['category'] ?: ucfirst($item['type'])) ?></span>
                        <h2><a href="<?= e(url($item['url'])) ?>"><?= e($item['title']) ?></a></h2>
                        <p><?= e($item['short_description'] ?? $item['description'] ?? '') ?></p>
                        <form method="post" action="<?= e(url('/bookmarks/toggle.php')) ?>">
                            <?= csrf_field() ?>
                            <input type="hidden" name="content_type" value="<?= e($item['type']) ?>">
                            <input type="hidden" name="content_id" value="<?= e((string) $item['id']) ?>">
                            <input type="hidden" name="return_url" value="<?= e(url('/bookmarks/index.php' . ($typeFilter !== '' ? '?type=' . urlencode($typeFilter) : ''))) ?>">
                            <button class="button button-secondary button-small" type="submit">Remove</button>
                        </form>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No bookmarks found</h2>
            <p>Open a process, equipment, chemical, diagram, video, quiz or glossary item and bookmark it for later.</p>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
