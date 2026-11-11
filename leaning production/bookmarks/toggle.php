<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/config/functions.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    http_response_code(403);
    exit('Invalid or expired bookmark request.');
}

$type = trim((string) ($_POST['content_type'] ?? ''));
$contentId = filter_input(INPUT_POST, 'content_id', FILTER_VALIDATE_INT);
$returnUrl = trim((string) ($_POST['return_url'] ?? '/'));
$allowed = [
    'process' => ['table'=>'processes','status'=>'published'],
    'equipment' => ['table'=>'equipment','status'=>null],
    'chemical' => ['table'=>'chemicals','status'=>null],
    'diagram' => ['table'=>'process_flow_diagrams','status'=>null],
    'video' => ['table'=>'videos','status'=>'published'],
    'quiz' => ['table'=>'quizzes','status'=>'published'],
    'glossary' => ['table'=>'glossary','status'=>null],
];

if (!isset($allowed[$type]) || !$contentId) {
    http_response_code(400);
    exit('Invalid bookmark target.');
}

$config = $allowed[$type];
$table = $config['table'];
$sql = 'SELECT id FROM ' . $table . ' WHERE id=?';
$parameters = [$contentId];
if ($config['status'] !== null) {
    $sql .= ' AND status=?';
    $parameters[] = $config['status'];
}
$sql .= ' LIMIT 1';
$check = $pdo->prepare($sql);
$check->execute($parameters);
if (!$check->fetch()) {
    http_response_code(404);
    exit('Bookmark content not found.');
}

$userId = (int) current_user()['id'];
$existing = $pdo->prepare(
    'SELECT id FROM bookmarks WHERE user_id=? AND content_type=? AND content_id=? LIMIT 1'
);
$existing->execute([$userId,$type,$contentId]);
$row = $existing->fetch();

if ($row) {
    $delete = $pdo->prepare('DELETE FROM bookmarks WHERE id=? AND user_id=?');
    $delete->execute([(int) $row['id'],$userId]);
} else {
    $insert = $pdo->prepare(
        'INSERT INTO bookmarks (user_id,content_type,content_id) VALUES (?,?,?)'
    );
    $insert->execute([$userId,$type,$contentId]);
}

$allowedPrefix = IPLA_BASE_URL ?: '';
if ($returnUrl === '' || strpos($returnUrl, '//') === 0 || ($allowedPrefix !== '' && strpos($returnUrl, $allowedPrefix) !== 0)) {
    $returnUrl = '/bookmarks/index.php';
}
header('Location: ' . $returnUrl);
exit;
