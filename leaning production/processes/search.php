<?php
declare(strict_types=1);

require dirname(__DIR__) . '/config/functions.php';

$query = trim((string) ($_GET['q'] ?? ''));
redirect('/processes/index.php' . ($query !== '' ? '?q=' . urlencode($query) : ''));
