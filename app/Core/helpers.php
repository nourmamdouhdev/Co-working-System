<?php

declare(strict_types=1);

function config(string $key, mixed $default = null): mixed
{
    $segments = explode('.', $key);
    $value = $GLOBALS['app_config'] ?? [];

    foreach ($segments as $segment) {
        if (!is_array($value) || !array_key_exists($segment, $value)) {
            return $default;
        }
        $value = $value[$segment];
    }

    return $value;
}

function app_url(string $path = ''): string
{
    $baseUrl = rtrim((string) config('app.url', ''), '/');
    $path = '/' . ltrim($path, '/');

    return $baseUrl . ($path === '/' ? '' : $path);
}

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['_csrf_token'])) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['_csrf_token'];
}

function csrf_field(): string
{
    $token = csrf_token();
    return '<input type="hidden" name="_csrf" value="' . e($token) . '">';
}

function verify_csrf_token(?string $token): bool
{
    $sessionToken = $_SESSION['_csrf_token'] ?? '';
    return is_string($token) && $sessionToken !== '' && hash_equals($sessionToken, $token);
}

function flash(string $key, ?string $value = null): ?string
{
    if ($value !== null) {
        $_SESSION['_flash'][$key] = $value;
        return null;
    }

    $message = $_SESSION['_flash'][$key] ?? null;
    unset($_SESSION['_flash'][$key]);

    return $message;
}

function with_old_input(array $input): void
{
    $_SESSION['_old'] = $input;
}

function old(string $key, string $default = ''): string
{
    return (string) ($_SESSION['_old'][$key] ?? $default);
}

function clear_old_input(): void
{
    unset($_SESSION['_old']);
}

function redirect(string $path): never
{
    header('Location: ' . app_url($path));
    exit;
}

function render(string $view, array $data = []): void
{
    $viewPath = ROOT_PATH . '/app/Views/' . $view . '.php';
    if (!is_file($viewPath)) {
        http_response_code(500);
        echo 'View not found: ' . e($view);
        return;
    }

    extract($data, EXTR_SKIP);
    include ROOT_PATH . '/app/Views/layouts/main.php';
}

function request_method(): string
{
    return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
}

function request_uri_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    return is_string($path) ? $path : '/';
}

function request_input(string $key, mixed $default = null): mixed
{
    return $_POST[$key] ?? $_GET[$key] ?? $default;
}

function normalize_phone(string $phone): string
{
    $phone = trim($phone);
    if ($phone === '') {
        return '';
    }

    $leadingPlus = str_starts_with($phone, '+');
    $digits = preg_replace('/\D+/', '', $phone);
    if (!is_string($digits)) {
        return '';
    }

    return $leadingPlus ? '+' . $digits : $digits;
}

function format_money(float $amount, ?string $currency = null): string
{
    $currency = $currency ?: 'USD';
    return $currency . ' ' . number_format($amount, 2);
}
