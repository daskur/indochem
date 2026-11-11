<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
if(!verify_csrf()){http_response_code(403);exit('Invalid or expired form token.');}
$action=(string)($_POST['action']??'');$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($action==='delete'&&$id){$pdo->prepare('DELETE FROM equipment WHERE id=?')->execute([$id]);$message='Equipment deleted.';}
elseif($action==='save'){
$name=trim((string)($_POST['name']??''));$category=trim((string)($_POST['category']??''));$description=trim((string)($_POST['description']??''));$function=trim((string)($_POST['function']??''));
if($name===''){$message='Equipment name is required.';}elseif($id){$pdo->prepare('UPDATE equipment SET name=?,category=?,description=?,function=? WHERE id=?')->execute([$name,$category?:null,$description?:null,$function?:null,$id]);$message='Equipment updated.';}
else{$pdo->prepare('INSERT INTO equipment (name,category,description,`function`) VALUES (?,?,?,?)')->execute([$name,$category?:null,$description?:null,$function?:null]);$message='Equipment added.';}
}}
$search=trim((string)($_GET['q']??''));$sql='SELECT * FROM equipment';$params=[];if($search){$sql.=' WHERE name LIKE ? OR category LIKE ?';$params[]='%'.$search.'%';$params[]='%'.$search.'%';}$sql.=' ORDER BY name';$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
$pageTitle='Manage Equipment';$isAdmin=true;require dirname(__DIR__,2).'/includes/header.php';require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>Equipment</h1></div></header>
<?php if($message):?><div class="alert <?=strpos($message,'required')!==false?'alert-error':'alert-success'?>"><?=e($message)?></div><?php endif;?>
<section class="card admin-form-card"><div class="card-body"><h2>Add equipment</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><div class="form-grid"><div class="form-group"><label>Name</label><input class="form-control" name="name" maxlength="150" required></div><div class="form-group"><label>Category</label><input class="form-control" name="category" maxlength="100"></div></div><div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="4"></textarea></div><div class="form-group"><label>Function</label><textarea class="form-control" name="function" rows="4"></textarea></div><button class="button button-primary">Add equipment</button></form></div></section>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search equipment"><button class="button button-primary">Search</button></form>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Name</th><th>Category</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['name'])?></td><td><?=e($row['category']?:'-')?></td><td><form method="post" onsubmit="return confirm('Delete this equipment record?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$row['id']?>"><button class="button button-danger button-small">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
