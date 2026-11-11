<?php
if (!isset($bookmarkType, $bookmarkId)) {
    return;
}
$bookmarkType = (string) $bookmarkType;
$bookmarkId = (int) $bookmarkId;
$isBookmarked = false;

if (is_logged_in()) {
    $bookmarkStatement = $pdo->prepare(
        'SELECT id FROM bookmarks WHERE user_id=? AND content_type=? AND content_id=? LIMIT 1'
    );
    $bookmarkStatement->execute([(int) current_user()['id'],$bookmarkType,$bookmarkId]);
    $isBookmarked = (bool) $bookmarkStatement->fetch();
}

$returnUrl = $_SERVER['REQUEST_URI'] ?? '/';
?>
<?php if (is_logged_in()): ?>
    <form method="post" action="<?= e(url('/bookmarks/toggle.php')) ?>" class="bookmark-form">
        <?= csrf_field() ?>
        <input type="hidden" name="content_type" value="<?= e($bookmarkType) ?>">
        <input type="hidden" name="content_id" value="<?= e((string) $bookmarkId) ?>">
        <input type="hidden" name="return_url" value="<?= e($returnUrl) ?>">
        <button class="button <?= $isBookmarked ? 'button-secondary' : 'button-primary' ?>" type="submit">
            <?= $isBookmarked ? '★ Bookmarked' : '☆ Bookmark' ?>
        </button>
    </form>
<?php else: ?>
    <a class="button button-secondary" href="<?= e(url('/login.php')) ?>">☆ Sign in to bookmark</a>
<?php endif; ?>
