<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

header('Cache-Control: no-store, max-age=0');
header('Referrer-Policy: no-referrer');

$token = strtolower(trim((string) ($_GET['token'] ?? $_POST['token'] ?? '')));
$errors = [];
$tokenIsValid = preg_match('/\A[a-f0-9]{64}\z/', $token) === 1;

if ($tokenIsValid) {
    $statement = $pdo->prepare(
        'SELECT user_id FROM password_resets WHERE token_hash=? AND expires_at > NOW() LIMIT 1'
    );
    $statement->execute([hash('sha256', $token)]);
    $tokenIsValid = (bool) $statement->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenIsValid) {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if (strlen($password) < 8 || strlen($password) > 4096) {
            $errors[] = 'Password must be between 8 and 4096 characters.';
        } elseif (!preg_match('/[A-Z]/', $password)
            || !preg_match('/[a-z]/', $password)
            || !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must include an uppercase letter, a lowercase letter and a number.';
        }
        if (!hash_equals($password, $confirmPassword)) {
            $errors[] = 'Passwords do not match.';
        }

        if (!$errors) {
            try {
                $pdo->beginTransaction();
                $statement = $pdo->prepare(
                    'SELECT user_id FROM password_resets
                     WHERE token_hash=? AND expires_at > NOW()
                     LIMIT 1 FOR UPDATE'
                );
                $statement->execute([hash('sha256', $token)]);
                $reset = $statement->fetch();

                if (!$reset) {
                    $pdo->rollBack();
                    $tokenIsValid = false;
                } else {
                    $update = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
                    $update->execute([
                        password_hash($password, PASSWORD_DEFAULT),
                        (int) $reset['user_id'],
                    ]);

                    $delete = $pdo->prepare('DELETE FROM password_resets WHERE user_id=?');
                    $delete->execute([(int) $reset['user_id']]);
                    $pdo->commit();

                    flash('success', 'Your password has been reset. You can now sign in.');
                    redirect('/login.php');
                }
            } catch (Throwable $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                error_log('IPLA password reset completion failed: ' . $exception->getMessage());
                $errors[] = 'Password reset is temporarily unavailable. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Choose a New Password';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="card auth-card" aria-labelledby="reset-password-title">
        <div class="card-body">
            <h1 id="reset-password-title">Choose a new password</h1>
            <?php if (!$tokenIsValid): ?>
                <div class="alert alert-error" role="alert">
                    This password reset link is invalid or has expired. Request a new one to continue.
                </div>
                <div class="auth-links">
                    <a href="<?= e(url('/forgot_password.php')) ?>">Request another reset link</a>
                </div>
            <?php else: ?>
                <?php foreach ($errors as $error): ?>
                    <div class="alert alert-error"><?= e($error) ?></div>
                <?php endforeach; ?>
                <p>Use at least 8 characters, including an uppercase letter, a lowercase letter, and a number.</p>
                <form method="post" action="<?= e(url('/reset_password.php')) ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= e($token) ?>">
                    <div class="form-group">
                        <label for="password">New password</label>
                        <input
                            class="form-control"
                            id="password"
                            name="password"
                            type="password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm new password</label>
                        <input
                            class="form-control"
                            id="confirm_password"
                            name="confirm_password"
                            type="password"
                            minlength="8"
                            autocomplete="new-password"
                            required
                        >
                    </div>
                    <button class="button button-primary" type="submit">Reset password</button>
                </form>
            <?php endif; ?>
            <div class="auth-links">
                <a href="<?= e(url('/login.php')) ?>">Back to sign in</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
