<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
$processId = filter_input(INPUT_GET, 'process_id', FILTER_VALIDATE_INT) ?: null;

$processes = $pdo->query(
    'SELECT DISTINCT p.id,p.title FROM quizzes q
     JOIN processes p ON p.id=q.process_id
     WHERE q.status="published" AND p.status="published"
     ORDER BY p.title'
)->fetchAll();

$sql = 'SELECT q.*,p.title AS process_title,
               COUNT(DISTINCT qq.id) AS question_count,
               COALESCE(SUM(qq.marks),0) AS total_marks
        FROM quizzes q
        JOIN processes p ON p.id=q.process_id AND p.status="published"
        LEFT JOIN quiz_questions qq ON qq.quiz_id=q.id
        WHERE q.status="published"';
$parameters = [];

if ($query !== '') {
    $sql .= ' AND (q.title LIKE ? OR q.description LIKE ? OR p.title LIKE ?)';
    $like = '%' . $query . '%';
    array_push($parameters, $like, $like, $like);
}
if ($processId) {
    $sql .= ' AND q.process_id=?';
    $parameters[] = $processId;
}
$sql .= ' GROUP BY q.id ORDER BY q.title ASC';

$statement = $pdo->prepare($sql);
$statement->execute($parameters);
$quizzes = $statement->fetchAll();

$attempts = [];
if (is_logged_in()) {
    $attemptStatement = $pdo->prepare(
        'SELECT quiz_id,percentage,passed,completed_at
         FROM quiz_attempts WHERE user_id=? ORDER BY completed_at DESC,id DESC'
    );
    $attemptStatement->execute([(int) current_user()['id']]);
    foreach ($attemptStatement->fetchAll() as $attempt) {
        if (!isset($attempts[(int) $attempt['quiz_id']])) {
            $attempts[(int) $attempt['quiz_id']] = $attempt;
        }
    }
}

$pageTitle = 'Quiz and Practice';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Quiz and Practice</span>
    </nav>

    <header class="module-heading">
        <div>
            <span class="section-kicker">Knowledge check</span>
            <h1>Quiz and Practice</h1>
            <p>Test your understanding of industrial processes and review answer explanations.</p>
        </div>
        <span class="module-count"><?= count($quizzes) ?> quiz<?= count($quizzes) === 1 ? '' : 'zes' ?></span>
    </header>

    <form class="process-filter quiz-filter" method="get" action="<?= e(url('/quizzes/index.php')) ?>">
        <div>
            <label for="quiz-search">Search quizzes</label>
            <input class="form-control" id="quiz-search" name="q" type="search"
                   value="<?= e($query) ?>" placeholder="Title, topic or process">
        </div>
        <div>
            <label for="quiz-process">Process</label>
            <select class="form-control" id="quiz-process" name="process_id">
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

    <?php if ($quizzes): ?>
        <div class="quiz-grid">
            <?php foreach ($quizzes as $quiz): ?>
                <?php $attempt = $attempts[(int) $quiz['id']] ?? null; ?>
                <article class="card quiz-card">
                    <div class="card-body">
                        <span class="badge"><?= e($quiz['process_title']) ?></span>
                        <h2><a href="<?= e(url('/quizzes/start.php?id=' . $quiz['id'])) ?>"><?= e($quiz['title']) ?></a></h2>
                        <p><?= e($quiz['description'] ?: 'Check your understanding with this practice quiz.') ?></p>
                        <div class="quiz-meta">
                            <span><?= e((string) $quiz['question_count']) ?> question<?= (int) $quiz['question_count'] === 1 ? '' : 's' ?></span>
                            <span><?= e((string) $quiz['total_marks']) ?> mark<?= (int) $quiz['total_marks'] === 1 ? '' : 's' ?></span>
                            <span><?= e((string) $quiz['passing_score']) ?>% to pass</span>
                        </div>
                        <?php if ($attempt): ?>
                            <div class="quiz-attempt-status <?= (int) $attempt['passed'] === 1 ? 'passed' : 'failed' ?>">
                                Latest: <?= e((string) $attempt['percentage']) ?>% —
                                <?= (int) $attempt['passed'] === 1 ? 'Passed' : 'Not passed' ?>
                            </div>
                        <?php endif; ?>
                        <a class="button button-primary button-small" href="<?= e(url('/quizzes/start.php?id=' . $quiz['id'])) ?>">
                            <?= $attempt ? 'Try again' : 'Start quiz' ?>
                        </a>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <h2>No quizzes found</h2>
            <p>Try a different search term or process filter.</p>
            <a class="button button-secondary" href="<?= e(url('/quizzes/index.php')) ?>">Clear filters</a>
        </div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
