<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$userId = (int) current_user()['id'];

$message = flash('siwes_success');

$logStatement = $pdo->prepare(
    'SELECT * FROM siwes_logbooks WHERE user_id=? ORDER BY log_date DESC,id DESC LIMIT 20'
);
$logStatement->execute([$userId]);
$logbooks = $logStatement->fetchAll();

$reportStatement = $pdo->prepare(
    'SELECT * FROM siwes_reports WHERE user_id=? ORDER BY updated_at DESC,id DESC'
);
$reportStatement->execute([$userId]);
$reports = $reportStatement->fetchAll();

$countStatement = $pdo->prepare(
    'SELECT COUNT(*) AS total,COALESCE(SUM(log_date>=DATE_SUB(CURDATE(),INTERVAL 30 DAY)),0) AS recent
     FROM siwes_logbooks WHERE user_id=?'
);
$countStatement->execute([$userId]);
$counts = $countStatement->fetch();

$pageTitle = 'SIWES Corner';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container siwes-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>SIWES Corner</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Industrial training record</span>
            <h1>SIWES Corner</h1>
            <p>Maintain daily training records and prepare reports for industrial attachment.</p>
        </div>
    </header>

    <?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>

    <div class="siwes-summary">
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['total']) ?></strong><span>Log entries</span></div></article>
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['recent']) ?></strong><span>Entries in 30 days</span></div></article>
        <article class="card"><div class="card-body"><strong><?= e((string) count($reports)) ?></strong><span>Reports</span></div></article>
    </div>

    <section class="process-section siwes-section">
        <div class="section-heading">
            <div><span class="section-kicker">Daily record</span><h2>Logbook entries</h2></div>
            <a class="button button-primary button-small" href="<?= e(url('/siwes/logbook.php')) ?>">Add entry</a>
        </div>

        <?php if ($logbooks): ?>
            <div class="siwes-table-wrap">
                <table class="siwes-table">
                    <thead><tr><th>Date</th><th>Activity</th><th>Skills learned</th><th>Status</th><th>Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($logbooks as $entry): ?>
                        <tr>
                            <td><?= e(date('d M Y', strtotime($entry['log_date']))) ?></td>
                            <td><strong><?= e($entry['activity']) ?></strong><small><?= e($entry['description'] ?: '') ?></small></td>
                            <td><?= e($entry['skills_learned'] ?: 'Not recorded') ?></td>
                            <td><?= !empty($entry['supervisor_comment']) ? '<span class="badge">Commented</span>' : '<span class="badge">Pending</span>' ?></td>
                            <td>
                                <a href="<?= e(url('/siwes/logbook.php?id=' . $entry['id'])) ?>">Edit</a>
                                <form method="post" action="<?= e(url('/siwes/logbook.php')) ?>" class="inline-delete" onsubmit="return confirm('Delete this logbook entry?');">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= e((string) $entry['id']) ?>">
                                    <button type="submit">Delete</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state"><h2>No logbook entries yet</h2><p>Add your first industrial-training activity.</p></div>
        <?php endif; ?>
    </section>

    <section class="process-section siwes-section">
        <div class="section-heading">
            <div><span class="section-kicker">Written documentation</span><h2>SIWES reports</h2></div>
            <a class="button button-primary button-small" href="<?= e(url('/siwes/report.php')) ?>">New report</a>
        </div>
        <?php if ($reports): ?>
            <div class="siwes-report-grid">
                <?php foreach ($reports as $report): ?>
                    <article class="card siwes-report-card">
                        <div class="card-body">
                            <span class="report-status <?= e($report['status']) ?>"><?= e(ucfirst($report['status'])) ?></span>
                            <h3><a href="<?= e(url('/siwes/report.php?id=' . $report['id'])) ?>"><?= e($report['title']) ?></a></h3>
                            <p><?= e($report['report_type']) ?></p>
                            <small>Updated <?= e(date('d M Y', strtotime($report['updated_at']))) ?></small>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state"><h2>No reports created</h2><p>Create a draft and submit it when it is ready.</p></div>
        <?php endif; ?>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
