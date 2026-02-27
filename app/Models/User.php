<?php

declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{
    public static function findById(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsername(string $username): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function listAll(): array
    {
        $stmt = self::db()->query('SELECT id, name, username, role, is_active, created_at FROM users ORDER BY id DESC');
        return $stmt->fetchAll() ?: [];
    }

    public static function create(string $name, string $username, string $passwordHash, string $role): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO users (name, username, password_hash, role, is_active, created_at, updated_at)
             VALUES (:name, :username, :password_hash, :role, 1, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'username' => $username,
            'password_hash' => $passwordHash,
            'role' => $role,
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateActiveStatus(int $id, bool $isActive): void
    {
        $stmt = self::db()->prepare('UPDATE users SET is_active = :is_active, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'is_active' => $isActive ? 1 : 0,
        ]);
    }
}
