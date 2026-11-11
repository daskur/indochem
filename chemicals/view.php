<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$chemicalId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$chemicalId) {
    http_response_code(404);
    exit('Chemical not found.');
}

$statement = $pdo->prepare('SELECT * FROM chemicals WHERE id = ? LIMIT 1');
$statement->execute([$chemicalId]);
$chemical = $statement->fetch();
if (!$chemical) {
    http_response_code(404);
    exit('Chemical not found.');
}

$searchTerms = array_filter([
    trim((string) $chemical['name']),
    trim((string) $chemical['chemical_formula']),
]);

$relatedProcesses = [];
$relatedReactions = [];
if ($searchTerms) {
    $clauses = [];
    $parameters = [];
    foreach ($searchTerms as $term) {
        if ($term === '') {
            continue;
        }
        $clauses[] = '(r.reactants LIKE ? OR r.products LIKE ? OR r.equation LIKE ? OR r.reaction_title LIKE ?)';
        $like = '%' . $term . '%';
        array_push($parameters, $like, $like, $like, $like);
    }

    if ($clauses) {
        $reactionSql = 'SELECT r.*,p.id AS process_id,p.title AS process_title,p.category
                        FROM process_reactions r
                        JOIN processes p ON p.id = r.process_id
                        WHERE p.status = "published" AND ' . implode(' OR ', $clauses) . '
                        ORDER BY p.title,r.id';
        $reactionStatement = $pdo->prepare($reactionSql);
        $reactionStatement->execute($parameters);
        $relatedReactions = $reactionStatement->fetchAll();

        $seenProcesses = [];
        foreach ($relatedReactions as $reaction) {
            $processId = (int) $reaction['process_id'];
            if (!isset($seenProcesses[$processId])) {
                $seenProcesses[$processId] = true;
                $relatedProcesses[] = [
                    'id' => $processId,
                    'title' => $reaction['process_title'],
                    'category' => $reaction['category'],
                ];
            }
        }
    }
}

$pageTitle = $chemical['name'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container chemical-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/chemicals/index.php')) ?>">Chemical Library</a><span aria-hidden="true">/</span>
        <span><?= e($chemical['name']) ?></span>
    </nav>

    <header class="chemical-header">
        <div class="chemical-hero-formula" aria-label="Chemical formula">
            <?= e($chemical['chemical_formula'] ?: substr($chemical['name'], 0, 2)) ?>
        </div>
        <div>
            <?php if (!empty($chemical['physical_state'])): ?>
                <span class="badge"><?= e($chemical['physical_state']) ?></span>
            <?php endif; ?>
            <h1><?= e($chemical['name']) ?></h1>
            <?php if (!empty($chemical['cas_number'])): ?>
                <p class="chemical-cas">CAS Number: <?= e($chemical['cas_number']) ?></p>
            <?php endif; ?>
        </div>
        <div class="detail-bookmark-action">
            <?php $bookmarkType='chemical'; $bookmarkId=(int)$chemical['id']; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
        </div>
    </header>

    <div class="equipment-layout">
        <aside class="equipment-sidebar" aria-label="Chemical sections">
            <nav>
                <a href="#properties">Properties</a>
                <a href="#uses">Uses</a>
                <a href="#hazards">Hazards</a>
                <a href="#storage">Storage</a>
                <a href="#safety">Safety Information</a>
                <a href="#reactions">Related Reactions</a>
                <a href="#processes">Related Processes</a>
            </nav>
        </aside>

        <div class="equipment-content">
            <section class="process-section" id="properties">
                <h2>Properties</h2>
                <?php if (!empty($chemical['properties'])): ?>
                    <p><?= nl2br(e($chemical['properties'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Chemical properties have not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="uses">
                <h2>Uses</h2>
                <?php if (!empty($chemical['uses'])): ?>
                    <p><?= nl2br(e($chemical['uses'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Industrial uses have not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="hazards">
                <h2>Hazards</h2>
                <?php if (!empty($chemical['hazards'])): ?>
                    <div class="hazard-card">
                        <span class="badge">HAZARD</span>
                        <p><?= nl2br(e($chemical['hazards'])) ?></p>
                    </div>
                <?php else: ?>
                    <p class="not-provided">Hazard information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="storage">
                <h2>Storage Methods</h2>
                <?php if (!empty($chemical['storage_methods'])): ?>
                    <p><?= nl2br(e($chemical['storage_methods'])) ?></p>
                <?php else: ?>
                    <p class="not-provided">Storage methods have not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="safety">
                <h2>Safety Information</h2>
                <?php if (!empty($chemical['safety_information'])): ?>
                    <div class="safety-card">
                        <span class="badge">SAFETY</span>
                        <p><?= nl2br(e($chemical['safety_information'])) ?></p>
                    </div>
                <?php else: ?>
                    <p class="not-provided">Safety information has not been added yet.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="reactions">
                <h2>Related Chemical Reactions</h2>
                <?php if ($relatedReactions): ?>
                    <?php foreach ($relatedReactions as $reaction): ?>
                        <article class="reaction-card card">
                            <div class="card-body">
                                <span class="badge"><?= e($reaction['process_title']) ?></span>
                                <h3><?= e($reaction['reaction_title']) ?></h3>
                                <?php if (!empty($reaction['equation'])): ?>
                                    <code><?= e($reaction['equation']) ?></code>
                                <?php endif; ?>
                                <?php if (!empty($reaction['description'])): ?>
                                    <p><?= nl2br(e($reaction['description'])) ?></p>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="not-provided">No matching process reactions are currently linked.</p>
                <?php endif; ?>
            </section>

            <section class="process-section" id="processes">
                <h2>Related Industrial Processes</h2>
                <?php if ($relatedProcesses): ?>
                    <div class="info-grid">
                        <?php foreach ($relatedProcesses as $process): ?>
                            <article class="card info-card">
                                <div class="card-body">
                                    <?php if (!empty($process['category'])): ?>
                                        <span class="badge"><?= e($process['category']) ?></span>
                                    <?php endif; ?>
                                    <h3><a href="<?= e(url('/processes/view.php?id=' . $process['id'])) ?>"><?= e($process['title']) ?></a></h3>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="not-provided">No published industrial processes are currently linked to this chemical.</p>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
