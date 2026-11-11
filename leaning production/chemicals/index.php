<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$physicalState = trim((string) ($_GET['state'] ?? ''));

$states = $pdo->query(
    'SELECT DISTINCT physical_state FROM chemicals
     WHERE physical_state IS NOT NULL AND physical_state <> ""
     ORDER BY physical_state ASC'
)->fetchAll();

$sql = 'SELECT id,name,chemical_formula,cas_number,physical_state,properties,uses,image
        FROM chemicals WHERE 1=1';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (name LIKE ? OR chemical_formula LIKE ? OR cas_number LIKE ? OR properties LIKE ? OR uses LIKE ? OR hazards LIKE ?)';
    $likeQuery = '%' . $query . '%';
    array_push($parameters, $likeQuery, $likeQuery, $likeQuery, $likeQuery, $likeQuery, $likeQuery);
}
if ($physicalState !== '') {
    $sql .= ' AND physical_state = ?';
    $parameters[] = $physicalState;
}
$sql .= ' ORDER BY name ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$chemicals = $statement->fetchAll();

$pageTitle = 'Chemical Library';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a>
        <span aria-hidden="true">/</span>
        <span>Chemical Library</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Industrial chemistry reference</span>
            <h1>Chemical Library</h1>
            <p>Explore chemical formulas, properties, uses, hazards and safe storage methods.</p>
        </div>
        <span class="module-count"><?= count($chemicals) ?> chemical<?= count($chemicals) === 1 ? '' : 's' ?></span>
    </header>

    <form class="process-filter equipment-filter" method="get" action="<?= e(url('/chemicals/index.php')) ?>">
        <div>
            <label for="chemical-search">Search chemicals</label>
            <input class="form-control" id="chemical-search" name="q" type="search" value="<?= e($query) ?>" placeholder="Name, formula, CAS number or use">
        </div>
        <div>
            <label for="chemical-state">Physical state</label>
            <select class="form-control" id="chemical-state" name="state">
                <option value="">All states</option>
                <?php foreach ($states as $row): ?>
                    <option value="<?= e($row['physical_state']) ?>" <?= $physicalState === $row['physical_state'] ? 'selected' : '' ?>>
                        <?= e($row['physical_state']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="button button-primary" type="submit">Filter</button>
    </form>

    <?php if ($chemicals): ?>
        <div class="library-grid">
            <?php foreach ($chemicals as $chemical): ?>
                <article class="card library-card chemical-card">
                    <div class="chemical-formula" aria-label="Chemical formula">
                        <span><?= e($chemical['chemical_formula'] ?: substr($chemical['name'], 0, 2)) ?></span>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($chemical['physical_state'])): ?>
                            <span class="badge"><?= e($chemical['physical_state']) ?></span>
                        <?php endif; ?>
                        <h2><a href="<?= e(url('/chemicals/view.php?id=' . $chemical['id'])) ?>"><?= e($chemical['name']) ?></a></h2>
                        <?php if (!empty($chemical['cas_number'])): ?>
                            <small>CAS <?= e($chemical['cas_number']) ?></small>
                        <?php endif; ?>
                        <p><?= e($chemical['uses'] ?: $chemical['properties'] ?: 'Explore this chemical in detail.') ?></p>
                        <a class="text-link" href="<?= e(url('/chemicals/view.php?id=' . $chemical['id'])) ?>">View chemical →</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No chemicals found</h2>
            <p>Try a different search term or physical state.</p>
            <a class="button button-secondary" href="<?= e(url('/chemicals/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
