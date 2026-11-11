<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();

$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    if(!verify_csrf()){http_response_code(403);exit('Invalid or expired form token.');}
    $id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
    $action=(string)($_POST['action']??'');
    if($id && $id === (int)current_user()['id'] && in_array($action,['role','delete'],true)){
        $message='You cannot change or delete your own administrator account.';
    }elseif($id && $action==='role'){
        $role=(string)($_POST['role']??'student');
        if(in_array($role,['student','admin'],true)){
            $pdo->prepare('UPDATE users SET role=? WHERE id=?')->execute([$role,$id]);
            $message='User role updated.';
        }
    }elseif($id && $action==='delete'){
        $pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
        $message='User deleted.';
    }
}
$search=trim((string)($_GET['q']??''));
$sql='SELECT id,full_name,email,role,created_at FROM users';
$params=[];
if($search!== ''){$sql.=' WHERE full_name LIKE ? OR email LIKE ?';$params[]='%'.$search.'%';$params[]='%'.$search.'%';}
$sql.=' ORDER BY created_at DESC,id DESC';
$statement=$pdo->prepare($sql);$statement->execute($params);$users=$statement->fetchAll();

$pageTitle='Manage Users';$isAdmin=true;require dirname(__DIR__,2).'/includes/header.php';
require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Accounts</span><h1>Users</h1></div></header>
<?php if($message):?><div class="alert alert-success"><?=e($message)?></div><?php endif;?>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search users"><button class="button button-primary">Search</button></form>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Joined</th><th>Actions</th></tr></thead><tbody>
<?php foreach($users as $user):?><tr><td><?=e($user['full_name'])?></td><td><?=e($user['email'])?></td><td><span class="badge"><?=e($user['role'])?></span></td><td><?=e(date('d M Y',strtotime($user['created_at'])))?></td><td class="admin-actions">
<form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="role"><input type="hidden" name="id" value="<?=$user['id']?>"><select name="role"><option value="student" <?= $user['role']==='student'?'selected':''?>>Student</option><option value="admin" <?= $user['role']==='admin'?'selected':''?>>Admin</option></select><button class="button button-secondary button-small">Update</button></form>
<form method="post" onsubmit="return confirm('Delete this user and all owned records?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$user['id']?>"><button class="button button-danger button-small">Delete</button></form>
</td></tr><?php endforeach;?>
</tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
