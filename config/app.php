<?php

declare(strict_types=1);

$env = static function (string $key, mixed $default = null): mixed {
    $value = getenv($key);
    return $value === false ? $default : $value;
};

return [
    'app' => [
        'name' => $env('APP_NAME', 'Co-working System'),
        'env' => $env('APP_ENV', 'production'),
        'debug' => filter_var($env('APP_DEBUG', '0'), FILTER_VALIDATE_BOOL),
        'url' => $env('APP_URL', 'http://localhost:8000'),
        'timezone' => $env('APP_TIMEZONE', 'UTC'),
    ],
    'db' => [
        'host' => $env('DB_HOST', '127.0.0.1'),
        'port' => (int) $env('DB_PORT', '3306'),
        'name' => $env('DB_NAME', 'coworking_system'),
        'user' => $env('DB_USER', 'root'),
        'pass' => $env('DB_PASS', ''),
        'charset' => $env('DB_CHARSET', 'utf8mb4'),
    ],
];
