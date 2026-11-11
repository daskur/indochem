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
    $notificationId = filter_input(INPUT_POST,'notification_id',FILTER_VALIDATE_INT);
    if ((string) ($_POST['action'] ?? '') === 'mark_all') {
        $update = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE user_id=? AND is_read=0');
        $update->execute([$userId]);
        flash('notification_success','All notifications marked as read.');
        redirect('/notifications/index.php');
    }
    if ($notificationId) {
        $update = $pdo->prepare('UPDATE notifications SET is_read=1 WHERE id=? AND user_id=?');
        $update->execute([$notificationId,$userId]);
    }
    redirect('/notifications/index.php');
}

$statement = $pdo->prepare(
    'SELECT * FROM notifications WHERE user_id=? ORDER BY created_at DESC,id DESC'
);
$statement->execute([$userId]);
$notifications = $statement->fetchAll();
$success = flash('notification_success');

$pageTitle = 'Notifications';
require dirname(__DIR__) . '/includes/header.php';
?>
<main id="main-content" class="container module-page notifications-page">
    <nav class="breadcrumbs" aria-label="Breadcrumb">
        <a href="<?= e(url('/index.php')) ?>">Home</a><span aria-hidden="true">/</span>
        <span>Notifications</span>
    </nav>
    <?php if ($success): ?><div class="alert alert-success"><?= e($success) ?></div><?php endif; ?>
    <header class="module-heading">
        <div><span class="section-kicker">Updates</span><h1>Notifications</h1></div>
        <?php if ($notifications): ?>
            <form method="post" action="<?= e(url('/notifications/index.php')) ?>">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="mark_all">
                <button class="button button-secondary button-small" type="submit">Mark all as read</button>
            </form>
        <?php endif; ?>
    </header>
    <?php if ($notifications): ?>
        <div class="notification-list">
            <?php foreach ($notifications as $notification): ?>
                <article class="card notification-card <?= (int) $notification['is_read'] === 0 ? 'unread' : '' ?>">
                    <div class="card-body">
                        <div>
                            <h2><?= e($notification['title']) ?></h2>
                            <?php if (!empty($notification['message'])): ?><p><?= nl2br(e($notification['message'])) ?></p><?php endif; ?>
                            <small><?= e(date('d M Y, H:i', strtotime($notification['created_at']))) ?></small>
                        </div>
                        <div class="notification-actions">
                            <?php
                            $link = trim((string) ($notification['link'] ?? ''));
                            if ($link !== '' && strpos($link,'/') === 0 && strpos($link,'//') !== 0):
                            ?>
                                <a class="button button-primary button-small" href="<?= e(url($link)) ?>">Open</a>
                            <?php endif; ?>
                            <?php if ((int) $notification['is_read'] === 0): ?>
                                <form method="post" action="<?= e(url('/notifications/index.php')) ?>">
                                    <?= csrf_field() ?>
                                    <input type="hidden" name="notification_id" value="<?= e((string) $notification['id']) ?>">
                                    <button class="button button-secondary button-small" type="submit">Mark read</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-state"><h2>No notifications</h2><p>New learning and account updates will appear here.</p></div>
    <?php endif; ?>
</main>
<?php require dirname(__DIR__) . '/includes/footer.php'; ?>
