<?php

declare(strict_types=1);

const ROOT_PATH = __DIR__ . '/..';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnvFile(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $key = trim($parts[0]);
        $value = trim($parts[1]);
        $value = trim($value, "\"'");

        if (getenv($key) === false) {
            putenv("{$key}={$value}");
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }
}

loadEnvFile(ROOT_PATH . '/.env');

spl_autoload_register(static function (string $class): void {
    $prefix = 'App\\';
    $baseDir = ROOT_PATH . '/app/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

require_once ROOT_PATH . '/app/Core/helpers.php';

$GLOBALS['app_config'] = require ROOT_PATH . '/config/app.php';

$timezone = config('app.timezone', 'UTC');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}

try {
    $dbTimezone = \App\Models\Setting::get('timezone');
    if (is_string($dbTimezone) && $dbTimezone !== '') {
        date_default_timezone_set($dbTimezone);
    }
} catch (Throwable $exception) {
    // Database may not be initialized yet.
}
