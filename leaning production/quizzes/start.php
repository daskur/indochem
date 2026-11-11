<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

$quizId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$quizId) {
    http_response_code(404);
    exit('Quiz not found.');
}

$quizStatement = $pdo->prepare(
    'SELECT q.*,p.title AS process_title
     FROM quizzes q
     JOIN processes p ON p.id=q.process_id AND p.status="published"
     WHERE q.id=? AND q.status="published" LIMIT 1'
);
$quizStatement->execute([$quizId]);
$quiz = $quizStatement->fetch();
if (!$quiz) {
    http_response_code(404);
    exit('Quiz not found.');
}

$questionStatement = $pdo->prepare(
    'SELECT * FROM quiz_questions WHERE quiz_id=? ORDER BY id'
);
$questionStatement->execute([$quizId]);
$questions = $questionStatement->fetchAll();

$questionsWithAnswers = [];
foreach ($questions as $question) {
    $answerStatement = $pdo->prepare('SELECT * FROM quiz_answers WHERE question_id=? ORDER BY id');
    $answerStatement->execute([(int) $question['id']]);
    $question['answers'] = $answerStatement->fetchAll();
    $questionsWithAnswers[] = $question;
}

$result = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_login();
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid or expired form token.');
    }

    $submittedAnswers = $_POST['answers'] ?? [];
    if (!is_array($submittedAnswers)) {
        $submittedAnswers = [];
    }

    $score = 0;
    $totalMarks = 0;
    $review = [];

    foreach ($questionsWithAnswers as $question) {
        $questionId = (int) $question['id'];
        $marks = (int) $question['marks'];
        $totalMarks += $marks;
        $chosenId = isset($submittedAnswers[$questionId]) ? (int) $submittedAnswers[$questionId] : 0;
        $chosenAnswer = null;
        $correctAnswer = null;
        $isCorrect = false;

        foreach ($question['answers'] as $answer) {
            if ((int) $answer['is_correct'] === 1 && $correctAnswer === null) {
                $correctAnswer = $answer;
            }
            if ((int) $answer['id'] === $chosenId) {
                $chosenAnswer = $answer;
            }
        }

        if ($chosenAnswer !== null && (int) $chosenAnswer['is_correct'] === 1) {
            $isCorrect = true;
            $score += $marks;
        }

        $review[] = [
            'question' => $question,
            'chosen' => $chosenAnswer,
            'correct' => $correctAnswer,
            'is_correct' => $isCorrect,
        ];
    }

    $percentage = $totalMarks > 0 ? round(($score / $totalMarks) * 100, 2) : 0.00;
    $passed = $percentage >= (int) $quiz['passing_score'];

    try {
        $pdo->beginTransaction();
        $insert = $pdo->prepare(
            'INSERT INTO quiz_attempts
             (user_id,quiz_id,score,total_marks,percentage,passed,completed_at)
             VALUES (?,?,?,?,?,?,NOW())'
        );
        $insert->execute([
            (int) current_user()['id'], $quizId, $score, $totalMarks, $percentage, $passed ? 1 : 0,
        ]);
        $attemptId = (int) $pdo->lastInsertId();
        $pdo->commit();
        $result = [
            'attempt_id' => $attemptId,
            'score' => $score,
            'total_marks' => $totalMarks,
            'percentage' => $percentage,
            'passed' => $passed,
            'review' => $review,
        ];
    } catch (Throwable $error) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $error;
    }
}

$pageTitle = $quiz['title'];
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container quiz-detail">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <a href="<?= e(url('/quizzes/index.php')) ?>">Quiz and Practice</a><span aria-hidden="true">/</span>
        <span><?= e($quiz['title']) ?></span>
    </nav>

    <?php if ($result !== null): ?>
        <section class="quiz-result card">
            <div class="card-body">
                <span class="quiz-result-status <?= $result['passed'] ? 'passed' : 'failed' ?>">
                    <?= $result['passed'] ? 'Passed' : 'Not passed' ?>
                </span>
                <h1><?= e((string) $result['percentage']) ?>%</h1>
                <p>You scored <?= e((string) $result['score']) ?> out of <?= e((string) $result['total_marks']) ?> marks.</p>
                <a class="button button-secondary" href="<?= e(url('/quizzes/index.php')) ?>">Back to quizzes</a>
                <a class="button button-primary" href="<?= e(url('/quizzes/start.php?id=' . $quizId)) ?>">Try again</a>
            </div>
        </section>

        <section class="quiz-review">
            <h2>Answer review</h2>
            <?php foreach ($result['review'] as $index => $item): ?>
                <article class="card quiz-review-card <?= $item['is_correct'] ? 'correct' : 'incorrect' ?>">
                    <div class="card-body">
                        <span><?= $item['is_correct'] ? 'Correct' : 'Incorrect' ?></span>
                        <h3><?= e((string) ($index + 1)) ?>. <?= e($item['question']['question']) ?></h3>
                        <p><strong>Your answer:</strong> <?= e($item['chosen']['answer'] ?? 'No answer selected') ?></p>
                        <?php if (!$item['is_correct']): ?>
                            <p><strong>Correct answer:</strong> <?= e($item['correct']['answer'] ?? 'Not available') ?></p>
                        <?php endif; ?>
                        <?php if (!empty($item['question']['explanation'])): ?>
                            <p class="quiz-explanation"><?= e($item['question']['explanation']) ?></p>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php else: ?>
        <header class="module-heading quiz-heading">
            <div>
                <span class="badge"><?= e($quiz['process_title']) ?></span>
                <h1><?= e($quiz['title']) ?></h1>
                <p><?= e($quiz['description'] ?: 'Answer each question and submit your attempt.') ?></p>
            </div>
                <?php $bookmarkType='quiz'; $bookmarkId=$quizId; require dirname(__DIR__).'/includes/bookmark_button.php'; ?>
                <span class="module-count"><?= count($questionsWithAnswers) ?> question<?= count($questionsWithAnswers) === 1 ? '' : 's' ?> · <?= e((string) $quiz['passing_score']) ?>% to pass</span>
        </header>

        <?php if (!$questionsWithAnswers): ?>
            <div class="empty-state">
                <h2>This quiz has no questions yet</h2>
                <a class="button button-secondary" href="<?= e(url('/quizzes/index.php')) ?>">Browse quizzes</a>
            </div>
        <?php else: ?>
            <?php if (!is_logged_in()): ?>
                <div class="quiz-login-note">
                    <strong>Sign in required.</strong>
                    <a href="<?= e(url('/login.php')) ?>">Login to submit your attempt</a>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= e(url('/quizzes/start.php?id=' . $quizId)) ?>" class="quiz-form">
                <?= csrf_field() ?>
                <?php foreach ($questionsWithAnswers as $index => $question): ?>
                    <fieldset class="card quiz-question">
                        <legend><?= e((string) ($index + 1)) ?>. <?= e($question['question']) ?></legend>
                        <span class="quiz-marks"><?= e((string) $question['marks']) ?> mark<?= (int) $question['marks'] === 1 ? '' : 's' ?></span>
                        <div class="quiz-answer-list">
                            <?php foreach ($question['answers'] as $answer): ?>
                                <label>
                                    <input type="radio"
                                           name="answers[<?= e((string) $question['id']) ?>]"
                                           value="<?= e((string) $answer['id']) ?>"
                                           required>
                                    <span><?= e($answer['answer']) ?></span>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </fieldset>
                <?php endforeach; ?>
                <button class="button button-primary" type="submit" <?= is_logged_in() ? '' : 'disabled' ?>>Submit quiz</button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
