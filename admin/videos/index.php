<?php
declare(strict_types=1);
require_once dirname(__DIR__,2).'/config/database.php';
require_once dirname(__DIR__,2).'/config/functions.php';
require_admin();
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
if(!verify_csrf()){http_response_code(403);exit('Invalid or expired form token.');}
$action=(string)($_POST['action']??'');$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if($action==='delete'&&$id){$pdo->prepare('DELETE FROM videos WHERE id=?')->execute([$id]);$message='Video deleted.';}
elseif($action==='save'){
$title=trim((string)($_POST['title']??''));$url=trim((string)($_POST['video_url']??''));$description=trim((string)($_POST['description']??''));$status=(string)($_POST['status']??'draft');$processId=filter_input(INPUT_POST,'process_id',FILTER_VALIDATE_INT);
if($title===''||$url===''){$message='Title and video URL are required.';}elseif(!in_array($status,['draft','published'],true)){$message='Invalid video status.';}elseif($id){$pdo->prepare('UPDATE videos SET title=?,video_url=?,description=?,status=?,process_id=? WHERE id=?')->execute([$title,$url,$description?:null,$status,$processId?:null,$id]);$message='Video updated.';}
else{$pdo->prepare('INSERT INTO videos (title,video_url,description,status,process_id) VALUES (?,?,?,?,?)')->execute([$title,$url,$description?:null,$status,$processId?:null]);$message='Video added.';}
}}
$search=trim((string)($_GET['q']??''));$sql='SELECT v.*,p.title AS process_title FROM videos v LEFT JOIN processes p ON p.id=v.process_id';$params=[];if($search){$sql.=' WHERE v.title LIKE ?';$params[]='%'.$search.'%';}$sql.=' ORDER BY v.created_at DESC,v.id DESC';$s=$pdo->prepare($sql);$s->execute($params);$rows=$s->fetchAll();
$processes=$pdo->query('SELECT id,title FROM processes ORDER BY title')->fetchAll();
$pageTitle='Manage Videos';$isAdmin=true;require dirname(__DIR__,2).'/includes/header.php';require dirname(__DIR__,2).'/includes/admin_layout.php';
?>
<header class="module-heading"><div><span class="section-kicker">Content</span><h1>Videos</h1></div></header>
<?php if($message):?><div class="alert <?=strpos($message,'required')!==false||strpos($message,'Invalid')!==false?'alert-error':'alert-success'?>"><?=e($message)?></div><?php endif;?>
<section class="card admin-form-card"><div class="card-body"><h2>Add video</h2><form method="post"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="save"><div class="form-grid"><div class="form-group"><label>Title</label><input class="form-control" name="title" maxlength="180" required></div><div class="form-group"><label>Video URL</label><input class="form-control" name="video_url" maxlength="500" required></div></div><div class="form-grid"><div class="form-group"><label>Process</label><select class="form-control" name="process_id"><option value="">None</option><?php foreach($processes as $process):?><option value="<?=$process['id']?>"><?=e($process['title'])?></option><?php endforeach;?></select></div><div class="form-group"><label>Status</label><select class="form-control" name="status"><option value="draft">Draft</option><option value="published">Published</option></select></div></div><div class="form-group"><label>Description</label><textarea class="form-control" name="description" rows="4"></textarea></div><button class="button button-primary">Add video</button></form></div></section>
<form class="admin-search" method="get"><input class="form-control" name="q" value="<?=e($search)?>" placeholder="Search videos"><button class="button button-primary">Search</button></form>
<div class="admin-table-wrap"><table class="data-table"><thead><tr><th>Title</th><th>Process</th><th>Status</th><th>Actions</th></tr></thead><tbody><?php foreach($rows as $row):?><tr><td><?=e($row['title'])?></td><td><?=e($row['process_title']?:'-')?></td><td><span class="badge"><?=e($row['status'])?></span></td><td><form method="post" onsubmit="return confirm('Delete this video?')"><input type="hidden" name="csrf_token" value="<?=e(csrf_token())?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?=$row['id']?>"><button class="button button-danger button-small">Delete</button></form></td></tr><?php endforeach;?></tbody></table></div>
<?php require dirname(__DIR__,2).'/includes/admin_layout_end.php';require dirname(__DIR__,2).'/includes/footer.php';?>
