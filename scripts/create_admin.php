<?php

declare(strict_types=1);

require __DIR__ . '/../app/bootstrap.php';

use App\Models\User;

if (PHP_SAPI !== 'cli') {
    echo "This script must run from CLI.\n";
    exit(1);
}

if ($argc < 4) {
    echo "Usage: php scripts/create_admin.php \"Full Name\" username password\n";
    exit(1);
}

[$script, $name, $username, $password] = $argv;

if (User::findByUsername($username) !== null) {
    echo "Username already exists.\n";
    exit(1);
}

$userId = User::create($name, $username, password_hash($password, PASSWORD_DEFAULT), 'admin');
echo "Admin user created with ID {$userId}.\n";
