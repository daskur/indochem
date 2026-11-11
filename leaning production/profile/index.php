<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();
$userId = (int) current_user()['id'];
$errors = [];
$success = flash('profile_success');

$statement = $pdo->prepare(
    'SELECT id,full_name,email,role,profile_image,created_at FROM users WHERE id=? LIMIT 1'
);
$statement->execute([$userId]);
$profile = $statement->fetch();
if (!$profile) {
    http_response_code(404);
    exit('Profile not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid or expired form token.');
    }

    $action = (string) ($_POST['action'] ?? 'details');

    if ($action === 'password') {
        $currentPassword = (string) ($_POST['current_password'] ?? '');
        $newPassword = (string) ($_POST['new_password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        $passwordStatement = $pdo->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
        $passwordStatement->execute([$userId]);
        $passwordRow = $passwordStatement->fetch();

        if (!$passwordRow || !password_verify($currentPassword, $passwordRow['password'])) {
            $errors[] = 'The current password is incorrect.';
        } elseif (strlen($newPassword) < 8) {
            $errors[] = 'The new password must be at least 8 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'The new password confirmation does not match.';
        } else {
            $update = $pdo->prepare('UPDATE users SET password=? WHERE id=?');
            $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $userId]);
            flash('profile_success', 'Password changed successfully.');
            redirect('/profile/index.php');
        }
    } else {
        $fullName = trim((string) ($_POST['full_name'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));

        if ($fullName === '') {
            $errors[] = 'Full name is required.';
        } elseif (strlen($fullName) > 120) {
            $errors[] = 'Full name must not exceed 120 characters.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
            $errors[] = 'Enter a valid email address.';
        } else {
            $duplicate = $pdo->prepare('SELECT id FROM users WHERE email=? AND id<>? LIMIT 1');
            $duplicate->execute([$email,$userId]);
            if ($duplicate->fetch()) {
                $errors[] = 'That email address is already in use.';
            } else {
                $profileImage = $profile['profile_image'];

                if (!empty($_FILES['profile_image']['name'])) {
                    $file = $_FILES['profile_image'];
                    if (!is_uploaded_file($file['tmp_name'])) {
                        $errors[] = 'The profile image upload is invalid.';
                    } elseif ($file['size'] > 2 * 1024 * 1024) {
                        $errors[] = 'The profile image must be 2 MB or smaller.';
                    } else {
                        $finfo = new finfo(FILEINFO_MIME_TYPE);
                        $mime = $finfo->file($file['tmp_name']);
                        $allowed = [
                            'image/jpeg' => 'jpg',
                            'image/png' => 'png',
                            'image/webp' => 'webp',
                        ];
                        if (!isset($allowed[$mime])) {
                            $errors[] = 'Use a JPG, PNG or WebP profile image.';
                        } else {
                            $directory = IPLA_ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'profiles';
                            if (!is_dir($directory) && !mkdir($directory, 0755, true) && !is_dir($directory)) {
                                $errors[] = 'Unable to prepare the profile upload folder.';
                            } else {
                                $filename = 'user-' . $userId . '-' . bin2hex(random_bytes(8)) . '.' . $allowed[$mime];
                                $destination = $directory . DIRECTORY_SEPARATOR . $filename;
                                if (!move_uploaded_file($file['tmp_name'], $destination)) {
                                    $errors[] = 'The profile image could not be uploaded.';
                                } else {
                                    if (!empty($profileImage)) {
                                        $oldPath = IPLA_ROOT_PATH . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, ltrim($profileImage, '/'));
                                        if (is_file($oldPath) && strpos(realpath($oldPath) ?: '', realpath(IPLA_ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads') ?: 'not-upload') === 0) {
                                            unlink($oldPath);
                                        }
                                    }
                                    $profileImage = 'uploads/profiles/' . $filename;
                                }
                            }
                        }
                    }
                }

                if (!$errors) {
                    $update = $pdo->prepare(
                        'UPDATE users SET full_name=?,email=?,profile_image=? WHERE id=?'
                    );
                    $update->execute([$fullName,$email,$profileImage,$userId]);

                    $_SESSION['user']['full_name'] = $fullName;
                    $_SESSION['user']['email'] = $email;
                    $_SESSION['user']['profile_image'] = $profileImage;
                    $profile['full_name'] = $fullName;
                    $profile['email'] = $email;
                    $profile['profile_image'] = $profileImage;

                    flash('profile_success', 'Profile details updated.');
                    redirect('/profile/index.php');
                }
            }
        }
    }
}

$progressStatement = $pdo->prepare(
    'SELECT up.*,p.title,p.category
     FROM user_progress up JOIN processes p ON p.id=up.process_id
     WHERE up.user_id=? AND p.status="published"
     ORDER BY up.updated_at DESC LIMIT 8'
);
$progressStatement->execute([$userId]);
$progress = $progressStatement->fetchAll();

$attemptStatement = $pdo->prepare(
    'SELECT qa.*,q.title AS quiz_title,p.title AS process_title
     FROM quiz_attempts qa
     JOIN quizzes q ON q.id=qa.quiz_id
     JOIN processes p ON p.id=q.process_id
     WHERE qa.user_id=?
     ORDER BY qa.completed_at DESC,qa.id DESC LIMIT 5'
);
$attemptStatement->execute([$userId]);
$attempts = $attemptStatement->fetchAll();

$countStatement = $pdo->prepare(
    'SELECT
       (SELECT COUNT(*) FROM user_progress WHERE user_id=? AND completed=1) AS completed_processes,
       (SELECT COUNT(*) FROM bookmarks WHERE user_id=? ) AS bookmarks,
       (SELECT COUNT(*) FROM siwes_logbooks WHERE user_id=? ) AS log_entries,
       (SELECT COUNT(*) FROM certificates WHERE user_id=? ) AS certificates,
       (SELECT COUNT(*) FROM siwes_reports WHERE user_id=? ) AS reports'
);
$countStatement->execute([$userId,$userId,$userId,$userId,$userId]);
$counts = $countStatement->fetch();

$pageTitle = 'My Profile';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container profile-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>My Profile</span>
    </nav>

    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>

    <header class="profile-header">
        <div class="profile-avatar">
            <?php if (!empty($profile['profile_image'])): ?>
                <img src="<?= e(url('/' . ltrim($profile['profile_image'], '/'))) ?>" alt="<?= e($profile['full_name']) ?>">
            <?php else: ?>
                <span><?= e(strtoupper(substr($profile['full_name'], 0, 1))) ?></span>
            <?php endif; ?>
        </div>
        <div>
            <span class="badge"><?= e(ucfirst($profile['role'])) ?></span>
            <h1><?= e($profile['full_name']) ?></h1>
            <p><?= e($profile['email']) ?></p>
            <small>Member since <?= e(date('d M Y', strtotime($profile['created_at']))) ?></small>
        </div>
    </header>

    <div class="profile-summary">
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['completed_processes']) ?></strong><span>Processes completed</span></div></article>
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['log_entries']) ?></strong><span>SIWES entries</span></div></article>
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['bookmarks']) ?></strong><span>Bookmarks</span></div></article>
        <article class="card"><div class="card-body"><strong><?= e((string) $counts['certificates']) ?></strong><span>Certificates</span></div></article>
    </div>

    <div class="profile-layout">
        <section class="process-section profile-settings">
            <h2>Account details</h2>
            <form method="post" action="<?= e(url('/profile/index.php')) ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="details">
                <div class="form-group">
                    <label for="profile-name">Full name</label>
                    <input class="form-control" id="profile-name" name="full_name" maxlength="120" value="<?= e($profile['full_name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="profile-email">Email address</label>
                    <input class="form-control" id="profile-email" name="email" type="email" maxlength="190" value="<?= e($profile['email']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="profile-image">Profile image</label>
                    <input class="form-control" id="profile-image" name="profile_image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp">
                    <small class="form-help">JPG, PNG or WebP, maximum 2 MB.</small>
                </div>
                <button class="button button-primary" type="submit">Save profile</button>
            </form>

            <hr>
            <h2>Change password</h2>
            <form method="post" action="<?= e(url('/profile/index.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="password">
                <div class="form-group"><label for="current-password">Current password</label><input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required></div>
                <div class="form-grid">
                    <div class="form-group"><label for="new-password">New password</label><input class="form-control" id="new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" required></div>
                    <div class="form-group"><label for="confirm-password">Confirm password</label><input class="form-control" id="confirm-password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required></div>
                </div>
                <button class="button button-secondary" type="submit">Change password</button>
            </form>
        </section>

        <div class="profile-history">
            <section class="process-section">
                <h2>Process progress</h2>
                <?php if ($progress): ?>
                    <div class="profile-progress-list">
                        <?php foreach ($progress as $item): ?>
                            <article>
                                <div><strong><a href="<?= e(url('/processes/view.php?id=' . $item['process_id'])) ?>"><?= e($item['title']) ?></a></strong><span><?= e((string) $item['progress_percentage']) ?>%</span></div>
                                <div class="progress-track"><span style="width:<?= e((string) $item['progress_percentage']) ?>%"></span></div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state compact"><p>No process progress recorded yet.</p></div>
                <?php endif; ?>
            </section>

            <section class="process-section">
                <h2>Recent quiz attempts</h2>
                <?php if ($attempts): ?>
                    <div class="profile-attempt-list">
                        <?php foreach ($attempts as $attempt): ?>
                            <article>
                                <strong><a href="<?= e(url('/quizzes/start.php?id=' . $attempt['quiz_id'])) ?>"><?= e($attempt['quiz_title']) ?></a></strong>
                                <span class="<?= (int) $attempt['passed'] === 1 ? 'passed' : 'failed' ?>">
                                    <?= e((string) $attempt['percentage']) ?>% — <?= (int) $attempt['passed'] === 1 ? 'Passed' : 'Not passed' ?>
                                </span>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state compact"><p>No quiz attempts recorded yet.</p></div>
                <?php endif; ?>
            </section>
        </div>
    </div>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
