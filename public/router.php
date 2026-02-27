<?php

declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $requested = __DIR__ . parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    if (is_file($requested)) {
        return false;
    }
}

require __DIR__ . '/index.php';
