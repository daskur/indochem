<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

$rootPath = str_replace('\\', '/', dirname(__DIR__));
$documentRoot = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
$relativeRoot = '';
if ($documentRoot !== '' && strpos($rootPath, rtrim($documentRoot, '/') . '/') === 0) {
    $relativeRoot = substr($rootPath, strlen(rtrim($documentRoot, '/')));
}
$baseUrl = '/' . trim($relativeRoot, '/');
$baseUrl = $baseUrl === '/' ? '' : $baseUrl;

define('IPLA_BASE_URL', $baseUrl);
define('IPLA_ROOT_PATH', dirname(__DIR__));
define('IPLA_UPLOAD_PATH', IPLA_ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
define('IPLA_APP_NAME', 'Industrial Process Learning App');
define('IPLA_APP_SHORT_NAME', 'IPLA');

$configuredPublicUrl = getenv('IPLA_PUBLIC_URL');
if ($configuredPublicUrl === false || trim($configuredPublicUrl) === '') {
    $serverName = (string) ($_SERVER['SERVER_NAME'] ?? '');
    $scheme = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
    $publicPath = str_replace(' ', '%20', IPLA_BASE_URL);
    $configuredPublicUrl = $serverName === '' ? '' : $scheme . '://' . $serverName . $publicPath;
}

define('IPLA_PUBLIC_URL', rtrim($configuredPublicUrl, '/'));
