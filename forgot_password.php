<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/functions.php';

if (is_logged_in()) {
    redirect('/dashboard.php');
}

$errors = [];
$email = '';
$requestSubmitted = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors[] = 'Your session expired. Please refresh and try again.';
    } else {
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $errors[] = 'Enter a valid email address.';
        } else {
            try {
                $statement = $pdo->prepare('SELECT id,email FROM users WHERE email=? LIMIT 1');
                $statement->execute([$email]);
                $user = $statement->fetch();

                if ($user) {
                    $recentRequest = $pdo->prepare(
                        'SELECT id FROM password_resets
                         WHERE user_id=? AND created_at > DATE_SUB(NOW(), INTERVAL 60 SECOND)
                         LIMIT 1'
                    );
                    $recentRequest->execute([(int) $user['id']]);

                    if (!$recentRequest->fetch()) {
                        $token = bin2hex(random_bytes(32));
                        $tokenHash = hash('sha256', $token);

                        $saveToken = $pdo->prepare(
                            'INSERT INTO password_resets (user_id,token_hash,expires_at)
                             VALUES (?,?,DATE_ADD(NOW(), INTERVAL 1 HOUR))
                             ON DUPLICATE KEY UPDATE
                                token_hash=VALUES(token_hash),
                                expires_at=DATE_ADD(NOW(), INTERVAL 1 HOUR),
                                created_at=CURRENT_TIMESTAMP'
                        );
                        $saveToken->execute([(int) $user['id'], $tokenHash]);

                        $publicUrl = IPLA_PUBLIC_URL;
                        $publicUrlParts = parse_url($publicUrl);
                        $validPublicUrl = is_array($publicUrlParts)
                            && isset($publicUrlParts['scheme'], $publicUrlParts['host'])
                            && in_array(strtolower($publicUrlParts['scheme']), ['http', 'https'], true)
                            && !isset($publicUrlParts['user'], $publicUrlParts['pass'], $publicUrlParts['query'], $publicUrlParts['fragment']);
                        $fromAddress = getenv('IPLA_MAIL_FROM') ?: 'no-reply@localhost';

                        if (!$validPublicUrl || !filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                            error_log('IPLA password reset email configuration is invalid.');
                            $deleteToken = $pdo->prepare('DELETE FROM password_resets WHERE user_id=? AND token_hash=?');
                            $deleteToken->execute([(int) $user['id'], $tokenHash]);
                        } else {
                            $resetUrl = $publicUrl
                                . str_replace(' ', '%20', url('/reset_password.php'))
                                . '?token=' . rawurlencode($token);
                            $subject = 'Reset your IPLA password';
                            $message = "A password reset was requested for your IPLA account.\n\n"
                                . "Use this link within one hour to choose a new password:\n"
                                . $resetUrl
                                . "\n\nIf you did not request this, you can ignore this email.";
                            $headers = [
                                'From: IPLA <' . $fromAddress . '>',
                                'Content-Type: text/plain; charset=UTF-8',
                                'X-Mailer: PHP/' . PHP_VERSION,
                            ];

                            if (!@mail($user['email'], $subject, $message, implode("\r\n", $headers))) {
                                error_log('IPLA password reset email could not be sent.');
                                $deleteToken = $pdo->prepare('DELETE FROM password_resets WHERE user_id=? AND token_hash=?');
                                $deleteToken->execute([(int) $user['id'], $tokenHash]);
                            }
                        }
                    }
                }

                $requestSubmitted = true;
            } catch (PDOException $exception) {
                error_log('IPLA password reset request failed: ' . $exception->getMessage());
                $errors[] = 'Password reset is temporarily unavailable. Please try again later.';
            } catch (Throwable $exception) {
                error_log('IPLA password reset request failed: ' . $exception->getMessage());
                $errors[] = 'Password reset is temporarily unavailable. Please try again later.';
            }
        }
    }
}

$pageTitle = 'Forgot Password';
require __DIR__ . '/includes/header.php';
?>
<main id="main-content" class="auth-page">
    <section class="card auth-card" aria-labelledby="forgot-password-title">
        <div class="card-body">
            <h1 id="forgot-password-title">Forgot your password?</h1>
            <?php if ($requestSubmitted): ?>
                <div class="alert alert-success" role="status">
                    If an account exists for that email, password reset instructions will be sent shortly.
                    Check your inbox and spam folder.
                </div>
            <?php endif; ?>
            <?php foreach ($errors as $error): ?>
                <div class="alert alert-error"><?= e($error) ?></div>
            <?php endforeach; ?>
            <?php if (!$requestSubmitted): ?>
                <p>Enter the email address linked to your student or administrator account.</p>
                <form method="post" action="<?= e(url('/forgot_password.php')) ?>">
                    <?= csrf_field() ?>
                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            class="form-control"
                            id="email"
                            name="email"
                            type="email"
                            maxlength="190"
                            autocomplete="email"
                            required
                            value="<?= e($email) ?>"
                        >
                    </div>
                    <button class="button button-primary" type="submit">Send reset link</button>
                </form>
            <?php endif; ?>
            <div class="auth-links">
                <a href="<?= e(url('/login.php')) ?>">Back to sign in</a>
            </div>
        </div>
    </section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
