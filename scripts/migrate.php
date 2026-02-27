<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Core\Database;

if (PHP_SAPI !== 'cli') {
    echo "This script must run from CLI.\n";
    exit(1);
}

$migrationFile = ROOT_PATH . '/database/migrations/001_init.sql';
if (!is_file($migrationFile)) {
    echo "Migration file not found: {$migrationFile}\n";
    exit(1);
}

$sql = file_get_contents($migrationFile);
if ($sql === false) {
    echo "Failed to read migration file.\n";
    exit(1);
}

try {
    $pdo = Database::connection();
    $pdo->exec($sql);
    echo "Migration completed successfully.\n";
    echo "Default admin username: admin\n";
    echo "Default admin password: admin123\n";
    echo "Change the password immediately after first login.\n";
} catch (Throwable $exception) {
    echo "Migration failed: " . $exception->getMessage() . "\n";
    exit(1);
}
