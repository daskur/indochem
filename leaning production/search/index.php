<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$like = '%' . $query . '%';
$results = ['processes' => [], 'equipment' => [], 'chemicals' => [], 'glossary' => [], 'videos' => [], 'quizzes' => []];

if ($query !== '') {
    $statement = $pdo->prepare(
        'SELECT id,title,category,short_description FROM processes
         WHERE status="published" AND (title LIKE ? OR category LIKE ? OR short_description LIKE ? OR description LIKE ?)
         ORDER BY title LIMIT 5'
    );
    $statement->execute([$like,$like,$like,$like]);
    $results['processes'] = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT id,name,category,description,function FROM equipment
         WHERE name LIKE ? OR category LIKE ? OR description LIKE ? OR function LIKE ?
         ORDER BY name LIMIT 5'
    );
    $statement->execute([$like,$like,$like,$like]);
    $results['equipment'] = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT id,name,chemical_formula,physical_state,uses FROM chemicals
         WHERE name LIKE ? OR chemical_formula LIKE ? OR cas_number LIKE ? OR uses LIKE ? OR hazards LIKE ?
         ORDER BY name LIMIT 5'
    );
    $statement->execute([$like,$like,$like,$like,$like]);
    $results['chemicals'] = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT id,term,definition,category FROM glossary
         WHERE term LIKE ? OR definition LIKE ? OR category LIKE ?
         ORDER BY term LIMIT 5'
    );
    $statement->execute([$like,$like,$like]);
    $results['glossary'] = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT v.id,v.title,v.description,p.title AS process_title,e.name AS equipment_name
         FROM videos v
         LEFT JOIN processes p ON p.id=v.process_id
         LEFT JOIN equipment e ON e.id=v.equipment_id
         WHERE v.status="published" AND (v.title LIKE ? OR v.description LIKE ?)
         ORDER BY v.title LIMIT 5'
    );
    $statement->execute([$like,$like]);
    $results['videos'] = $statement->fetchAll();

    $statement = $pdo->prepare(
        'SELECT q.id,q.title,q.description,p.title AS process_title
         FROM quizzes q
         JOIN processes p ON p.id=q.process_id AND p.status="published"
         WHERE q.status="published" AND (q.title LIKE ? OR q.description LIKE ? OR p.title LIKE ?)
         ORDER BY q.title LIMIT 5'
    );
    $statement->execute([$like,$like,$like]);
    $results['quizzes'] = $statement->fetchAll();
}

$total = array_sum(array_map('count', $results));
$pageTitle = 'Search IPLA';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container global-search-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Search</span>
    </nav>

    <header class="search-heading">
        <span class="section-kicker">Global search</span>
        <h1>Search IPLA</h1>
        <form class="search-bar global-search-bar" method="get" action="<?= e(url('/search/index.php')) ?>" role="search">
            <label class="sr-only" for="global-search-query">Search IPLA</label>
            <input class="form-control" id="global-search-query" name="q" type="search"
                   value="<?= e($query) ?>" placeholder="Processes, equipment, chemicals, terms or videos" autocomplete="off">
            <button class="button button-primary" type="submit">Search</button>
        </form>
    </header>

    <?php if ($query === ''): ?>
        <div class="empty-state"><h2>Enter a search term</h2><p>Search processes, equipment, chemicals, videos, quizzes and glossary terms.</p></div>
    <?php elseif ($total === 0): ?>
        <div class="empty-state">
            <h2>No results for “<?= e($query) ?>”</h2>
            <p>Check the spelling or try a shorter or broader search term.</p>
        </div>
    <?php else: ?>
        <p class="search-result-count"><?= $total ?> matching result<?= $total === 1 ? '' : 's' ?> for “<?= e($query) ?>”</p>
        <?php if ($results['processes']): ?>
            <section class="search-result-section"><h2>Processes</h2>
                <?php foreach ($results['processes'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/processes/view.php?id=' . $item['id'])) ?>"><strong><?= e($item['title']) ?></strong></a>
                        <p><?= e($item['short_description'] ?: $item['category']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($results['equipment']): ?>
            <section class="search-result-section"><h2>Equipment</h2>
                <?php foreach ($results['equipment'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/equipment/view.php?id=' . $item['id'])) ?>"><strong><?= e($item['name']) ?></strong></a>
                        <p><?= e($item['function'] ?: $item['description']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($results['chemicals']): ?>
            <section class="search-result-section"><h2>Chemicals</h2>
                <?php foreach ($results['chemicals'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/chemicals/view.php?id=' . $item['id'])) ?>"><strong><?= e($item['name']) ?><?= $item['chemical_formula'] ? ' — ' . e($item['chemical_formula']) : '' ?></strong></a>
                        <p><?= e($item['uses'] ?: $item['physical_state']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($results['videos']): ?>
            <section class="search-result-section"><h2>Videos</h2>
                <?php foreach ($results['videos'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/videos/view.php?id=' . $item['id'])) ?>"><strong><?= e($item['title']) ?></strong></a>
                        <p><?= e($item['description']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($results['quizzes']): ?>
            <section class="search-result-section"><h2>Quizzes</h2>
                <?php foreach ($results['quizzes'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/quizzes/start.php?id=' . $item['id'])) ?>"><strong><?= e($item['title']) ?></strong></a>
                        <p><?= e($item['description'] ?: $item['process_title']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($results['glossary']): ?>
            <section class="search-result-section"><h2>Glossary</h2>
                <?php foreach ($results['glossary'] as $item): ?>
                    <article class="card search-result-card"><div class="card-body">
                        <a href="<?= e(url('/glossary/index.php?q=' . urlencode($item['term']))) ?>"><strong><?= e($item['term']) ?></strong></a>
                        <p><?= e($item['definition']) ?></p>
                    </div></article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
