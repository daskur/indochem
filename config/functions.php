<?php

declare(strict_types=1);

require_once __DIR__ . '/constants.php';

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path = ''): string
{
    $path = '/' . ltrim($path, '/');
    return IPLA_BASE_URL === '' ? $path : IPLA_BASE_URL . $path;
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function is_logged_in(): bool
{
    return isset($_SESSION['user']);
}

function current_user(): ?array
{
    return is_logged_in() ? $_SESSION['user'] : null;
}

function require_login(): void
{
    if (!is_logged_in()) {
        redirect('/login.php');
    }
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Access denied.');
    }
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): bool
{
    return isset($_POST['csrf_token'], $_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
}

function flash(string $key, ?string $message = null): ?string
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return is_string($value) ? $value : null;
}

function slugify(string $value): string
{
    $value = strtolower(trim($value));
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    $value = preg_replace('/[^a-z0-9]+/', '-', $value ?? '');
    return trim($value ?? '', '-');
}
