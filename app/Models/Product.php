<?php

declare(strict_types=1);

namespace App\Models;

final class Product extends BaseModel
{
    public static function listAll(): array
    {
        $stmt = self::db()->query('SELECT id, name, unit_price, is_active, created_at FROM products ORDER BY name ASC');
        return $stmt->fetchAll() ?: [];
    }

    public static function listActive(): array
    {
        $stmt = self::db()->query('SELECT id, name, unit_price FROM products WHERE is_active = 1 ORDER BY name ASC');
        return $stmt->fetchAll() ?: [];
    }

    public static function findActiveByIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = self::db()->prepare(
            "SELECT id, name, unit_price FROM products WHERE is_active = 1 AND id IN ({$placeholders})"
        );
        $stmt->execute($ids);
        $rows = $stmt->fetchAll() ?: [];

        $byId = [];
        foreach ($rows as $row) {
            $byId[(int) $row['id']] = $row;
        }

        return $byId;
    }

    public static function create(string $name, float $unitPrice): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO products (name, unit_price, is_active, created_at, updated_at)
             VALUES (:name, :unit_price, 1, NOW(), NOW())'
        );
        $stmt->execute([
            'name' => $name,
            'unit_price' => $unitPrice,
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateActiveStatus(int $id, bool $isActive): void
    {
        $stmt = self::db()->prepare('UPDATE products SET is_active = :is_active, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'is_active' => $isActive ? 1 : 0,
        ]);
    }
}
