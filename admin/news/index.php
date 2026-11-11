<?php
declare(strict_types=1);
require_once dirname(__DIR__,2) . '/config/database.php';
require_once dirname(__DIR__,2) . '/config/functions.php';
require_admin();

$message = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) { http_response_code(403); exit('Invalid or expired form token.'); }
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        $delete = $pdo->prepare('DELETE FROM news WHERE id=?');
        $delete->execute([$id]);
        $message = 'News article deleted.';
    } elseif ($action === 'save') {
        $title = trim((string) ($_POST['title'] ?? ''));
        $content = trim((string) ($_POST['content'] ?? ''));
        $category = trim((string) ($_POST['category'] ?? ''));
        $status = (string) ($_POST['status'] ?? 'draft');
        if ($title === '' || $content === '') {
            $errors[] = 'Title and content are required.';
        } elseif (!in_array($status,['draft','published'],true)) {
            $errors[] = 'Invalid status.';
        } else {
            if ($id) {
                $update = $pdo->prepare('UPDATE news SET title=?,content=?,category=?,status=?,published_at=CASE WHEN ?="published" THEN COALESCE(published_at,NOW()) ELSE NULL END,updated_at=NOW() WHERE id=?');
                $update->execute([$title,$content,$category ?: null,$status,$status,$id]);
                $message = 'News article updated.';
            } else {
                $insert = $pdo->prepare('INSERT INTO news (title,content,category,author_id,status,published_at) VALUES (?,?,?,?,?,CASE WHEN ?="published" THEN NOW() ELSE NULL END)');
                $insert->execute([$title,$content,$category ?: null,(int) current_user()['id'],$status,$status]);
                $message = 'News article created.';
            }
        }
    }
}

$rows = $pdo->query('SELECT * FROM news ORDER BY created_at DESC,id DESC')->fetchAll();
$editId = filter_input(INPUT_GET,'edit',FILTER_VALIDATE_INT);
$edit = null;
if ($editId) { $statement=$pdo->prepare('SELECT * FROM news WHERE id=? LIMIT 1'); $statement->execute([$editId]); $edit=$statement->fetch(); }

$pageTitle='Manage News';
$isAdmin=true;
require dirname(__DIR__,2) . '/includes/header.php';
require dirname(__DIR__,2) . '/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>News</h1></div></header>
<?php foreach($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<?php if($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<section class="card admin-form-card"><div class="card-body">
<h2><?= $edit?'Edit article':'Create article' ?></h2>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="save"><input type="hidden" name="id" value="<?= e((string)($edit['id']??'')) ?>">
<div class="form-group"><label for="news-title">Title</label><input class="form-control" id="news-title" name="title" maxlength="180" value="<?= e($edit['title']??'') ?>" required></div>
<div class="form-grid"><div class="form-group"><label for="news-category">Category</label><input class="form-control" id="news-category" name="category" maxlength="100" value="<?= e($edit['category']??'') ?>"></div><div class="form-group"><label for="news-status">Status</label><select class="form-control" id="news-status" name="status"><option value="draft" <?= ($edit['status']??'draft')==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= ($edit['status']??'')==='published'?'selected':'' ?>>Published</option></select></div></div>
<div class="form-group"><label for="news-content">Content</label><textarea class="form-control" id="news-content" name="content" rows="8" required><?= e($edit['content']??'') ?></textarea></div>
<button class="button button-primary" type="submit"><?= $edit?'Save changes':'Publish article' ?></button><?php if($edit): ?> <a class="button button-secondary" href="<?= e(url('/admin/news/index.php')) ?>">Cancel</a><?php endif; ?></form>
</div></section>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Actions</th></tr></thead><tbody>
<?php foreach($rows as $row): ?><tr><td><?= e($row['title']) ?></td><td><?= e($row['category']?:'-') ?></td><td><span class="badge"><?= e($row['status']) ?></span></td><td class="admin-actions"><a class="button button-secondary button-small" href="<?= e(url('/admin/news/index.php?edit='.$row['id'])) ?>">Edit</a><form method="post" onsubmit="return confirm('Delete this article?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="button button-danger button-small">Delete</button></form></td></tr><?php endforeach; ?>
</tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php'; require dirname(__DIR__,2).'/includes/footer.php'; ?>
