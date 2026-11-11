<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
if(!verify_csrf()){http_response_code(403);exit('Invalid or expired form token.');}
$action=(string)($_POST['action']??'');$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($action==='delete'&&$id){$pdo->prepare('DELETE FROM chemicals WHERE id=?')->execute([$id]);$message='Chemical deleted.';}
elseif($action==='save'){
$name=trim((string)($_POST['name']??''));$formula=trim((string)($_POST['chemical_formula']??''));$state=trim((string)($_POST['physical_state']??''));$uses=trim((string)($_POST['uses']??''));$hazards=trim((string)($_POST['hazards']??''));
if($name===''){$message='Chemical name is required.';}elseif($id){$pdo->prepare('UPDATE chemicals SET name=?,chemical_formula=?,physical_state=?,uses=?,hazards=? WHERE id=?')->execute([$name,$formula?:null,$state?:null,$uses?:null,$hazards?:null,$id]);$message='Chemical updated.';}
else{$pdo->prepare('INSERT INTO chemicals (name,chemical_formula,physical_state,uses,hazards) VALUES (?,?,?,?,?)')->execute([$name,$formula?:null,$state?:null,$uses?:null,$hazards?:null]);$message='Chemical added.';}
}}
$search=trim((string)($_GET['q']??''));$sql='SELECT * FROM chemicals';$params=[];if($search){$sql.=' WHERE name LIKE ? OR chemical_formula LIKE ?';$params[]='%'.$search.'%';$params[]='%'.$search.'%';}$sql.=' ORDER BY name';$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
$pageTitle='Manage Chemicals';$isAdmin=true;require dirname(__DIR__,2).'/includes/header.php';require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>Chemicals</h1></div></header>
<?php if($message):?><div class="alert <?=strpos($message,'required')!==false?'alert-error':'alert-success'?>"><?=e($message)?></div><?php endif;?>
<section class="card admin-form-card"><div class="card-body"><h2>Add chemical</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><div class="form-grid"><div class="form-group"><label>Name</label><input class="form-control" name="name" maxlength="150" required></div><div class="form-group"><label>Formula</label><input class="form-control" name="chemical_formula" maxlength="100"></div></div><div class="form-grid"><div class="form-group"><label>Physical state</label><input class="form-control" name="physical_state" maxlength="50"></div><div class="form-group"><label>Uses</label><input class="form-control" name="uses"></div></div><div class="form-group"><label>Hazards</label><textarea class="form-control" name="hazards" rows="4"></textarea></div><button class="button button-primary">Add chemical</button></form></div></section>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search chemicals"><button class="button button-primary">Search</button></form>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Name</th><th>Formula</th><th>State</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['name'])?></td><td><?=e($row['chemical_formula']?:'-')?></td><td><?=e($row['physical_state']?:'-')?></td><td><form method="post" onsubmit="return confirm('Delete this chemical record?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$row['id']?>"><button class="button button-danger button-small">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
