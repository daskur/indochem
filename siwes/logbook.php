<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$userId = (int) current_user()['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid or expired form token.');
    }

    $action = (string) ($_POST['action'] ?? 'save');
    $entryId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

    if ($action === 'delete') {
        if (!$entryId) {
            http_response_code(404);
            exit('Logbook entry not found.');
        }
        $statement = $pdo->prepare('DELETE FROM siwes_logbooks WHERE id=? AND user_id=?');
        $statement->execute([$entryId,$userId]);
        flash('siwes_success', 'Logbook entry deleted.');
        redirect('/siwes/index.php');
    }

    $logDate = trim((string) ($_POST['log_date'] ?? ''));
    $activity = trim((string) ($_POST['activity'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $skills = trim((string) ($_POST['skills_learned'] ?? ''));
    $challenges = trim((string) ($_POST['challenges'] ?? ''));

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $logDate)) {
        $error = 'Enter a valid logbook date.';
    } elseif ($activity === '') {
        $error = 'Activity is required.';
    } elseif (strlen($activity) > 255) {
        $error = 'Activity must not exceed 255 characters.';
    } else {
        if ($entryId) {
            $statement = $pdo->prepare(
                'UPDATE siwes_logbooks
                 SET log_date=?,activity=?,description=?,skills_learned=?,challenges=?
                 WHERE id=? AND user_id=?'
            );
            $statement->execute([$logDate,$activity,$description,$skills,$challenges,$entryId,$userId]);
            flash('siwes_success', 'Logbook entry updated.');
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO siwes_logbooks
                 (user_id,log_date,activity,description,skills_learned,challenges)
                 VALUES (?,?,?,?,?,?)'
            );
            $statement->execute([$userId,$logDate,$activity,$description,$skills,$challenges]);
            flash('siwes_success', 'Logbook entry added.');
        }
        redirect('/siwes/index.php');
    }
} else {
    $entryId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
}

$entry = [
    'id' => null,'log_date' => date('Y-m-d'),'activity' => '',
    'description' => '', 'skills_learned' => '', 'challenges' => '', 'supervisor_comment' => '',
];
if (!empty($entryId)) {
    $statement = $pdo->prepare('SELECT * FROM siwes_logbooks WHERE id=? AND user_id=? LIMIT 1');
    $statement->execute([$entryId,$userId]);
    $storedEntry = $statement->fetch();
    if (!$storedEntry) {
        http_response_code(404);
        exit('Logbook entry not found.');
    }
    $entry = $storedEntry;
}

$pageTitle = $entry['id'] ? 'Edit Logbook Entry' : 'Add Logbook Entry';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container siwes-form-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/siwes/index.php')) ?>">SIWES Corner</a><span aria-hidden="true">/</span>
        <span><?= $entry['id'] ? 'Edit Entry' : 'Add Entry' ?></span>
    </nav>

    <section class="card siwes-form-card">
        <div class="card-body">
            <span class="section-kicker">Daily training record</span>
            <h1><?= $entry['id'] ? 'Edit logbook entry' : 'Add logbook entry' ?></h1>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="<?= e(url('/siwes/logbook.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="save">
                <?php if ($entry['id']): ?><input type="hidden" name="id" value="<?= e((string) $entry['id']) ?>"><?php endif; ?>
                <div class="form-grid">
                    <div class="form-group">
                        <label for="log-date">Date</label>
                        <input class="form-control" id="log-date" name="log_date" type="date"
                               value="<?= e($entry['log_date']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="activity">Activity</label>
                        <input class="form-control" id="activity" name="activity" maxlength="255"
                               value="<?= e($entry['activity']) ?>" required>
                    </div>
                </div>
                <div class="form-group"><label for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4"><?= e($entry['description']) ?></textarea></div>
                <div class="form-group"><label for="skills">Skills learned</label><textarea class="form-control" id="skills" name="skills_learned" rows="3"><?= e($entry['skills_learned']) ?></textarea></div>
                <div class="form-group"><label for="challenges">Challenges</label><textarea class="form-control" id="challenges" name="challenges" rows="3"><?= e($entry['challenges']) ?></textarea></div>
                <?php if (!empty($entry['supervisor_comment'])): ?>
                    <div class="supervisor-comment"><strong>Supervisor comment</strong><p><?= nl2br(e($entry['supervisor_comment'])) ?></p></div>
                <?php endif; ?>
                <div class="siwes-actions"><button class="button button-primary" type="submit">Save entry</button><a class="button button-secondary" href="<?= e(url('/siwes/index.php')) ?>">Cancel</a></div>
            </form>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
