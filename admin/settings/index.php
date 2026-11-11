<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();

$passwordError='';
$passwordSuccess='';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'password') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid or expired form token.');
    }
    $currentPassword=(string)($_POST['current_password']??'');
    $newPassword=(string)($_POST['new_password']??'');
    $confirmPassword=(string)($_POST['confirm_password']??'');
    $statement=$pdo->prepare('SELECT password FROM users WHERE id=? LIMIT 1');
    $statement->execute([(int)current_user()['id']]);
    $account=$statement->fetch();
    if (!$account || !password_verify($currentPassword,(string)$account['password'])) {
        $passwordError='The current password is incorrect.';
    } elseif (strlen($newPassword)<8) {
        $passwordError='The new password must be at least 8 characters.';
    } elseif ($newPassword!==$confirmPassword) {
        $passwordError='The new password confirmation does not match.';
    } else {
        $update=$pdo->prepare('UPDATE users SET password=? WHERE id=?');
        $update->execute([password_hash($newPassword,PASSWORD_DEFAULT),(int)current_user()['id']]);
        $passwordSuccess='Administrator password updated successfully.';
    }
}

$counts=[
'Database'=>$pdo->query('SELECT DATABASE()')->fetchColumn(),
'PHP'=>PHP_VERSION,
'Users'=>(int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
'Published processes'=>(int)$pdo->query("SELECT COUNT(*) FROM processes WHERE status='published'")->fetchColumn(),
'Published news'=>(int)$pdo->query("SELECT COUNT(*) FROM news WHERE status='published'")->fetchColumn(),
];
$pageTitle='Admin Settings';$isAdmin=true;
require dirname(__DIR__,2).'/includes/header.php';
require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">System</span><h1>Settings</h1></div></header>
<div class="settings-grid">
<?php foreach($counts as $label=>$value):?><article class="card"><div class="card-body"><strong><?=e((string)$value)?></strong><span><?=e($label)?></span></div></article><?php endforeach;?>
</div>
<section class="card admin-password-card"><div class="card-body">
<h2>Update administrator password</h2>
<?php if($passwordError):?><div class="alert alert-error"><?=e($passwordError)?></div><?php endif;?>
<?php if($passwordSuccess):?><div class="alert alert-success"><?=e($passwordSuccess)?></div><?php endif;?>
<form method="post">
<?=csrf_field()?>
<input type="hidden" name="action" value="password">
<div class="form-group"><label for="current-password">Current password</label><input class="form-control" id="current-password" name="current_password" type="password" autocomplete="current-password" required></div>
<div class="form-grid"><div class="form-group"><label for="new-password">New password</label><input class="form-control" id="new-password" name="new_password" type="password" minlength="8" autocomplete="new-password" required></div><div class="form-group"><label for="confirm-password">Confirm password</label><input class="form-control" id="confirm-password" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required></div></div>
<button class="button button-primary" type="submit">Update password</button>
</form>
</div></section>
<section class="card"><div class="card-body"><h2>Production reminders</h2><ul><li>Replace the seeded administrator account before deployment.</li><li>Configure database credentials through environment variables.</li><li>Enable HTTPS, secure backups and automated testing.</li><li>Review and publish only validated learning content.</li></ul></div></section>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
