<?php

declare(strict_types=1);

const ROOT_PATH = __DIR__ . '/..';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$timezone = config('app.timezone', 'Africa/Cairo');
if (is_string($timezone) && $timezone !== '') {
    date_default_timezone_set($timezone);
}

try {
    $dbTimezone = \App\Models\Setting::timezone();
    if (is_string($dbTimezone) && $dbTimezone !== '') {
        date_default_timezone_set($dbTimezone);
    }

    \App\Core\Database::syncSessionTimezone();
} catch (Throwable $exception) {
    // Database may not be initialized yet.
}
