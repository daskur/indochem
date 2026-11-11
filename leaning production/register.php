<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$errors = [];
$fullName = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    }

    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $password = (string) ($_POST['password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    if (strlen($fullName) < 2 || strlen($fullName) > 120) {
        $errors[] = 'Full name must be between 2 and 120 characters.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $errors[] = 'Enter a valid email address.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    }
    if (!preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must include an uppercase letter, a lowercase letter and a number.';
    }
    if (!hash_equals($password, $confirmPassword)) {
        $errors[] = 'Passwords do not match.';
    }

    if (!$errors) {
        try {
            $statement = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
            $statement->execute([$email]);
            if ($statement->fetch()) {
                $errors[] = 'An account already exists for this email address.';
            } else {
                $statement = $pdo->prepare(
                    'INSERT INTO users (full_name,email,password,role) VALUES (?, ?, ?, \'student\')'
                );
                $statement->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                flash('success', 'Account created successfully. Please sign in.');
                redirect('/login.php');
            }
        } catch (PDOException $exception) {
            error_log('IPLA registration failed: ' . $exception->getMessage());
            $errors[] = 'Registration is temporarily unavailable.';
        }
    }
}

$pageTitle = 'Create Account';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="card auth-card" aria-labelledby="register-title">
        <div class="card-body">
            <h1 id="register-title">Create your account</h1>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endforeach; ?>
            <form method="post" action="<?= e(url('/register.php')) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="full_name">Full name</label>
                    <input class="form-control" id="full_name" name="full_name" maxlength="120" autocomplete="name" required value="<?= e($fullName) ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= e($email) ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm password</label>
                    <input class="form-control" id="confirm_password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
                </div>
                <button class="button button-primary" type="submit">Create account</button>
            </form>
            <div class="auth-links">
                Already registered? <a href="<?= e(url('/login.php')) ?>">Sign in</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
