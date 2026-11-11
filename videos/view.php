<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$videoId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$videoId) {
    http_response_code(404);
    exit('Video tutorial not found.');
}

$statement = $pdo->prepare(
    'SELECT v.*,p.title AS process_title,e.name AS equipment_name
     FROM videos v
     LEFT JOIN processes p ON p.id=v.process_id
     LEFT JOIN equipment e ON e.id=v.equipment_id
     WHERE v.id=? AND v.status="published" LIMIT 1'
);
$statement->execute([$videoId]);
$video = $statement->fetch();
if (!$video) {
    http_response_code(404);
    exit('Video tutorial not found.');
}

$embedUrl = null;
$externalUrl = $video['video_url'];
$host = parse_url($externalUrl, PHP_URL_HOST) ?: '';
$path = parse_url($externalUrl, PHP_URL_PATH) ?: '';
$query = parse_url($externalUrl, PHP_URL_QUERY);

if (stripos($host, 'youtube.com') !== false || stripos($host, 'youtu.be') !== false) {
    $youtubeId = '';
    if (stripos($host, 'youtu.be') !== false) {
        $youtubeId = trim($path, '/');
    } elseif (!empty($query)) {
        parse_str($query, $youtubeQuery);
        $youtubeId = $youtubeQuery['v'] ?? '';
    }
    if ($youtubeId === '' && preg_match('#/embed/([A-Za-z0-9_-]+)#', $path, $matches)) {
        $youtubeId = $matches[1];
    }
    if ($youtubeId !== '') {
        $embedUrl = 'https://www.youtube.com/embed/' . rawurlencode($youtubeId);
    }
} elseif (stripos($host, 'vimeo.com') !== false) {
    if (preg_match('#/(\d+)#', $path, $matches)) {
        $embedUrl = 'https://player.vimeo.com/video/' . rawurlencode($matches[1]);
    }
}

$pageTitle = $video['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container video-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/videos/index.php')) ?>">Video Tutorials</a><span aria-hidden="true">/</span>
        <span><?= e($video['title']) ?></span>
    </nav>

    <header class="video-header">
        <div>
            <?php if (!empty($video['process_title'])): ?>
                <span class="badge"><?= e($video['process_title']) ?></span>
            <?php elseif (!empty($video['equipment_name'])): ?>
                <span class="badge"><?= e($video['equipment_name']) ?></span>
            <?php endif; ?>
            <h1><?= e($video['title']) ?></h1>
            <?php if (!empty($video['duration'])): ?><p class="video-duration"><?= e($video['duration']) ?></p><?php endif; ?>
        </div>
        <?php $bookmarkType='video'; $bookmarkId=$videoId; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
    </header>

    <section class="video-player" aria-label="<?= e($video['title']) ?>">
        <?php if ($embedUrl !== null): ?>
            <iframe src="<?= e($embedUrl) ?>" title="<?= e($video['title']) ?>"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen></iframe>
        <?php else: ?>
            <div class="empty-state">
                <h2>Open video tutorial</h2>
                <p>This video is hosted by an external provider.</p>
                <a class="button button-primary" href="<?= e($externalUrl) ?>" target="_blank" rel="noopener noreferrer">Open video</a>
            </div>
        <?php endif; ?>
    </section>

    <?php if (!empty($video['description'])): ?>
        <section class="process-section">
            <h2>About this tutorial</h2>
            <p><?= nl2br(e($video['description'])) ?></p>
        </section>
    <?php endif; ?>

    <section class="video-links process-section">
        <h2>Continue learning</h2>
        <?php if (!empty($video['process_id'])): ?>
            <a class="button button-secondary" href="<?= e(url('/processes/view.php?id=' . $video['process_id'])) ?>">Open process</a>
        <?php endif; ?>
        <?php if (!empty($video['equipment_id'])): ?>
            <a class="button button-secondary" href="<?= e(url('/equipment/view.php?id=' . $video['equipment_id'])) ?>">Open equipment</a>
        <?php endif; ?>
        <a class="button button-primary" href="<?= e(url('/videos/index.php')) ?>">Browse all videos</a>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
