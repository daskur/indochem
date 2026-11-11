<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
if(!verify_csrf()){http_response_code(403);exit('Invalid or expired form token.');}
$action=(string)($_POST['action']??'');$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($action==='delete'&&$id){$pdo->prepare('DELETE FROM glossary WHERE id=?')->execute([$id]);$message='Glossary term deleted.';}
elseif($action==='save'){
$term=trim((string)($_POST['term']??''));$definition=trim((string)($_POST['definition']??''));$category=trim((string)($_POST['category']??''));
if($term===''||$definition===''){$message='Term and definition are required.';}elseif($id){$pdo->prepare('UPDATE glossary SET term=?,definition=?,category=? WHERE id=?')->execute([$term,$definition,$category?:null,$id]);$message='Glossary term updated.';}
else{$pdo->prepare('INSERT INTO glossary (term,definition,category) VALUES (?,?,?)')->execute([$term,$definition,$category?:null]);$message='Glossary term added.';}
}}
$search=trim((string)($_GET['q']??''));$sql='SELECT * FROM glossary';$params=[];if($search){$sql.=' WHERE term LIKE ? OR definition LIKE ?';$params[]='%'.$search.'%';$params[]='%'.$search.'%';}$sql.=' ORDER BY term';$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
$pageTitle='Manage Glossary';$isAdmin=true;require dirname(__DIR__,2).'/includes/header.php';require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>Glossary</h1></div></header>
<?php if($message):?><div class="alert <?=strpos($message,'required')!==false?'alert-error':'alert-success'?>"><?=e($message)?></div><?php endif;?>
<section class="card admin-form-card"><div class="card-body"><h2>Add glossary term</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><div class="form-grid"><div class="form-group"><label>Term</label><input class="form-control" name="term" maxlength="180" required></div><div class="form-group"><label>Category</label><input class="form-control" name="category" maxlength="100"></div></div><div class="form-group"><label>Definition</label><textarea class="form-control" name="definition" rows="5" required></textarea></div><button class="button button-primary">Add term</button></form></div></section>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search glossary"><button class="button button-primary">Search</button></form>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Term</th><th>Category</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['term'])?></td><td><?=e($row['category']?:'-')?></td><td><form method="post" onsubmit="return confirm('Delete this glossary term?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$row['id']?>"><button class="button button-danger button-small">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
