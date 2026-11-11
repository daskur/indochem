<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $error = 'Your session expired. Please refresh and try again.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $password = (string) ($_POST['password'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Enter a valid email and password.';
        } else {
            try {
                $statement = $pdo->prepare(
                    'SELECT id,full_name,email,password,role,profile_image FROM users WHERE email = ? LIMIT 1'
                );
                $statement->execute([$email]);
                $user = $statement->fetch();

                if (!$user || !password_verify($password, $user['password'])) {
                    $error = 'Invalid email or password.';
                } else {
                    session_regenerate_id(true);
                    $_SESSION['user'] = [
                        'id' => (int) $user['id'],
                        'full_name' => $user['full_name'],
                        'email' => $user['email'],
                        'role' => $user['role'],
                        'profile_image' => $user['profile_image'],
                    ];
                    redirect('/dashboard.php');
                }
            } catch (PDOException $exception) {
                error_log('IPLA login failed: ' . $exception->getMessage());
                $error = 'Login is temporarily unavailable.';
            }
        }
    }
}

$success = flash('success');
$pageTitle = 'Sign In';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="card auth-card" aria-labelledby="login-title">
        <div class="card-body">
            <h1 id="login-title">Sign in to IPLA</h1>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= e($success) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endif; ?>
            <form method="post" action="<?= e(url('/login.php')) ?>">
                <?= csrf_field() ?>
                <div class="form-group">
                    <label for="email">Email address</label>
                    <input class="form-control" id="email" name="email" type="email" maxlength="190" autocomplete="email" required value="<?= e($email) ?>">
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
                </div>
                <button class="button button-primary" type="submit">Sign in</button>
            </form>
            <div class="auth-links">
                New student? <a href="<?= e(url('/register.php')) ?>">Create an account</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
