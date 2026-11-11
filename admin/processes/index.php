<?php
declare(strict_types=1);
require_once dirname(__DIR__,2) . '/config/database.php';
require_once dirname(__DIR__,2) . '/config/functions.php';
require_admin();

$message = '';
$errors = [];
$editProcess = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        http_response_code(403);
        exit('Invalid or expired form token.');
    }
    $action = (string) ($_POST['action'] ?? '');
    $id = filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    if ($action === 'delete' && $id) {
        $delete = $pdo->prepare('DELETE FROM processes WHERE id=?');
        $delete->execute([$id]);
        $message = 'Process deleted.';
    } elseif ($action === 'status' && $id) {
        $status = (string) ($_POST['status'] ?? 'draft');
        if (!in_array($status,['draft','published'],true)) $status = 'draft';
        $update = $pdo->prepare('UPDATE processes SET status=? WHERE id=?');
        $update->execute([$status,$id]);
        $message = 'Process status updated.';
    } elseif ($action === 'save') {
        $title = trim((string)($_POST['title'] ?? ''));
        $slug = strtolower(trim((string)($_POST['slug'] ?? '')));
        $category = trim((string)($_POST['category'] ?? ''));
        $shortDescription = trim((string)($_POST['short_description'] ?? ''));
        $description = trim((string)($_POST['description'] ?? ''));
        $status = (string)($_POST['status'] ?? 'draft');
        if ($title === '') {
            $errors[] = 'Process title is required.';
        }
        if (!in_array($status,['draft','published'],true)) {
            $status = 'draft';
        }
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug !== '' ? $slug : $title);
        $slug = trim((string)$slug, '-');
        if ($slug === '') {
            $errors[] = 'A valid process slug is required.';
        }
        if (!$errors && strlen($title) > 180) {
            $errors[] = 'Process title must not exceed 180 characters.';
        }
        if (!$errors && strlen($slug) > 190) {
            $errors[] = 'Process slug must not exceed 190 characters.';
        }
        if (!$errors) {
            $duplicateSql = 'SELECT id FROM processes WHERE slug=?';
            $duplicateParams = [$slug];
            if ($id) {
                $duplicateSql .= ' AND id<>?';
                $duplicateParams[] = $id;
            }
            $duplicateSql .= ' LIMIT 1';
            $duplicate = $pdo->prepare($duplicateSql);
            $duplicate->execute($duplicateParams);
            if ($duplicate->fetch()) {
                $errors[] = 'That process slug is already in use.';
            }
        }
        if (!$errors) {
            if ($id) {
                $save = $pdo->prepare('UPDATE processes SET title=?,slug=?,category=?,short_description=?,description=?,status=? WHERE id=?');
                $save->execute([$title,$slug,$category ?: null,$shortDescription ?: null,$description ?: null,$status,$id]);
                $message = 'Process updated.';
            } else {
                $save = $pdo->prepare('INSERT INTO processes (title,slug,category,short_description,description,status,created_by) VALUES (?,?,?,?,?,?,?)');
                $save->execute([$title,$slug,$category ?: null,$shortDescription ?: null,$description ?: null,$status,(int)current_user()['id']]);
                $message = 'Process created.';
            }
        } else {
            $editProcess = ['id'=>$id ?: null,'title'=>$title,'slug'=>$slug,'category'=>$category,'short_description'=>$shortDescription,'description'=>$description,'status'=>$status];
        }
    }
}

if (isset($_GET['edit']) && filter_var($_GET['edit'],FILTER_VALIDATE_INT)) {
    $load = $pdo->prepare('SELECT * FROM processes WHERE id=? LIMIT 1');
    $load->execute([(int)$_GET['edit']]);
    $editProcess = $load->fetch() ?: null;
}

$search = trim((string) ($_GET['q'] ?? ''));
$sql = 'SELECT p.*,u.full_name AS creator FROM processes p LEFT JOIN users u ON u.id=p.created_by';
$params = [];
if ($search !== '') {
    $sql .= ' WHERE p.title LIKE ? OR p.category LIKE ?';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
$sql .= ' ORDER BY p.updated_at DESC,p.id DESC';
$statement = $pdo->prepare($sql);
$statement->execute($params);
$rows = $statement->fetchAll();

$pageTitle = 'Manage Processes';
$isAdmin = true;
require dirname(__DIR__,2) . '/includes/header.php';
require dirname(__DIR__,2) . '/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>Processes</h1></div></header>
<?php if ($message): ?><div class="alert alert-success"><?= e($message) ?></div><?php endif; ?>
<?php foreach ($errors as $error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endforeach; ?>
<section class="card"><div class="card-body">
<h2><?= $editProcess ? 'Edit process' : 'Add process' ?></h2>
<form method="post">
<?= csrf_field() ?>
<input type="hidden" name="action" value="save">
<?php if (!empty($editProcess['id'])): ?><input type="hidden" name="id" value="<?= e((string)$editProcess['id']) ?>"><?php endif; ?>
<div class="form-grid">
<div class="form-group"><label for="process-title">Title</label><input class="form-control" id="process-title" name="title" maxlength="180" required value="<?= e((string)($editProcess['title'] ?? '')) ?>"></div>
<div class="form-group"><label for="process-slug">Slug <small>(generated from title if blank)</small></label><input class="form-control" id="process-slug" name="slug" maxlength="190" value="<?= e((string)($editProcess['slug'] ?? '')) ?>"></div>
</div>
<div class="form-grid">
<div class="form-group"><label for="process-category">Category</label><input class="form-control" id="process-category" name="category" maxlength="100" value="<?= e((string)($editProcess['category'] ?? '')) ?>"></div>
<div class="form-group"><label for="process-status">Status</label><select class="form-control" id="process-status" name="status"><option value="draft" <?= (($editProcess['status'] ?? 'draft')==='draft')?'selected':'' ?>>Draft</option><option value="published" <?= (($editProcess['status'] ?? '')==='published')?'selected':'' ?>>Published</option></select></div>
</div>
<div class="form-group"><label for="process-short-description">Short description</label><input class="form-control" id="process-short-description" name="short_description" maxlength="255" value="<?= e((string)($editProcess['short_description'] ?? '')) ?>"></div>
<div class="form-group"><label for="process-description">Full description</label><textarea class="form-control" id="process-description" name="description" rows="7"><?= e((string)($editProcess['description'] ?? '')) ?></textarea></div>
<button class="button button-primary" type="submit"><?= $editProcess ? 'Save changes' : 'Create process' ?></button>
<?php if ($editProcess): ?> <a class="button button-secondary" href="<?= e(url('/admin/processes/index.php')) ?>">Cancel edit</a><?php endif; ?>
</form>
</div></section>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?= e($search) ?>" placeholder="Search processes"><button class="button button-primary" type="submit">Search</button></form>
<div class="admin-table-wrap">
<table class="data-table"><thead><tr><th>Title</th><th>Category</th><th>Status</th><th>Updated</th><th>Actions</th></tr></thead><tbody>
<?php foreach ($rows as $row): ?>
<tr>
<td><strong><?= e($row['title']) ?></strong></td><td><?= e($row['category'] ?: '-') ?></td>
<td><span class="badge"><?= e($row['status']) ?></span></td><td><?= e(date('d M Y',strtotime($row['updated_at']))) ?></td>
<td class="admin-actions">
<a class="button button-secondary button-small" href="<?= e(url('/admin/processes/index.php?edit=' . $row['id'])) ?>">Edit</a>
<form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><select name="status" aria-label="Process status"><option value="draft" <?= $row['status']==='draft'?'selected':'' ?>>Draft</option><option value="published" <?= $row['status']==='published'?'selected':'' ?>>Published</option></select><button class="button button-secondary button-small" type="submit">Update</button></form>
<form method="post" onsubmit="return confirm('Delete this process and its learning content?')"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= e((string)$row['id']) ?>"><button class="button button-danger button-small" type="submit">Delete</button></form>
</td>
</tr>
<?php endforeach; ?>
</tbody></table></div>
<?php
require dirname(__DIR__,2) . '/includes/admin_layout_end.php';
require dirname(__DIR__,2) . '/includes/footer.php';
?>
