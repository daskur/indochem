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

    $reportId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $reportType = trim((string) ($_POST['report_type'] ?? ''));
    $title = trim((string) ($_POST['title'] ?? ''));
    $content = trim((string) ($_POST['content'] ?? ''));
    $action = (string) ($_POST['action'] ?? 'save');
    $status = $action === 'submit' ? 'submitted' : 'draft';

    if ($reportType === '' || $title === '') {
        $error = 'Report type and title are required.';
    } elseif (strlen($title) > 180) {
        $error = 'Report title must not exceed 180 characters.';
    } else {
        if ($reportId) {
            $statement = $pdo->prepare(
                'UPDATE siwes_reports SET report_type=?,title=?,content=?,status=?
                 WHERE id=? AND user_id=? AND status IN ("draft","rejected")'
            );
            $statement->execute([$reportType,$title,$content,$status,$reportId,$userId]);
            if ($statement->rowCount() === 0) {
                http_response_code(403);
                exit('This report cannot be edited in its current status.');
            }
        } else {
            $statement = $pdo->prepare(
                'INSERT INTO siwes_reports (user_id,report_type,title,content,status)
                 VALUES (?,?,?,?,?)'
            );
            $statement->execute([$userId,$reportType,$title,$content,$status]);
        }
        flash('siwes_success', $status === 'submitted' ? 'Report submitted for review.' : 'Report draft saved.');
        redirect('/siwes/index.php');
    }
}

$reportId = $reportId ?? filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$report = ['id'=>null,'report_type'=>'Daily Training Report','title'=>'','content'=>'','status'=>'draft'];
if (!empty($reportId)) {
    $statement = $pdo->prepare('SELECT * FROM siwes_reports WHERE id=? AND user_id=? LIMIT 1');
    $statement->execute([$reportId,$userId]);
    $storedReport = $statement->fetch();
    if (!$storedReport) {
        http_response_code(404);
        exit('Report not found.');
    }
    $report = $storedReport;
}

$pageTitle = $report['id'] ? 'Edit SIWES Report' : 'New SIWES Report';
require dirname(__DIR__) . '/includes/header.php';
$editable = $report['status'] === 'draft' || $report['status'] === 'rejected';
?>
<main id="main-content" class="container siwes-form-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/siwes/index.php')) ?>">SIWES Corner</a><span aria-hidden="true">/</span>
        <span><?= $report['id'] ? 'Edit Report' : 'New Report' ?></span>
    </nav>

    <section class="card siwes-form-card">
        <div class="card-body">
            <span class="section-kicker">Report documentation</span>
            <h1><?= e($report['title'] ?: 'New SIWES report') ?></h1>
            <p class="report-status-line">Status: <strong><?= e(ucfirst($report['status'])) ?></strong></p>
            <?php if (!empty($error)): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>

            <?php if (!$editable): ?>
                <article class="report-readonly">
                    <dl>
                        <dt>Report type</dt><dd><?= e($report['report_type']) ?></dd>
                        <dt>Content</dt><dd><?= nl2br(e($report['content'] ?: 'No report content has been recorded.')) ?></dd>
                    </dl>
                </article>
                <a class="button button-secondary" href="<?= e(url('/siwes/index.php')) ?>">Back to SIWES</a>
            <?php else: ?>
                <form method="post" action="<?= e(url('/siwes/report.php')) ?>">
                    <?= csrf_field() ?>
                    <?php if ($report['id']): ?><input type="hidden" name="id" value="<?= e((string) $report['id']) ?>"><?php endif; ?>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="report-type">Report type</label>
                            <select class="form-control" id="report-type" name="report_type" required>
                                <?php foreach (['Daily Training Report','Weekly Summary','Technical Report','Final SIWES Report'] as $type): ?>
                                    <option value="<?= e($type) ?>" <?= $report['report_type'] === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="report-title">Title</label>
                            <input class="form-control" id="report-title" name="title" maxlength="180" value="<?= e($report['title']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group"><label for="report-content">Report content</label><textarea class="form-control" id="report-content" name="content" rows="14"><?= e($report['content']) ?></textarea></div>
                    <div class="siwes-actions">
                        <button class="button button-secondary" name="action" value="save" type="submit">Save draft</button>
                        <button class="button button-primary" name="action" value="submit" type="submit" onclick="return confirm('Submit this report for review?');">Submit report</button>
                        <a class="button button-secondary" href="<?= e(url('/siwes/index.php')) ?>">Cancel</a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </section>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
