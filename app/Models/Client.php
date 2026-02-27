<?php

declare(strict_types=1);

namespace App\Models;

final class Client extends BaseModel
{
    public static function lockById(int $id): void
    {
        $stmt = self::db()->prepare('SELECT id FROM clients WHERE id = :id FOR UPDATE');
        $stmt->execute(['id' => $id]);
        $stmt->fetchColumn();
    }

    public static function findByPhone(string $phone): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM clients WHERE phone = :phone LIMIT 1');
        $stmt->execute(['phone' => $phone]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(string $fullName, string $phone, ?string $notes = null): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO clients (full_name, phone, notes, created_at, updated_at)
             VALUES (:full_name, :phone, :notes, NOW(), NOW())'
        );
        $stmt->execute([
            'full_name' => $fullName,
            'phone' => $phone,
            'notes' => $notes,
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function updateName(int $id, string $fullName): void
    {
        $stmt = self::db()->prepare('UPDATE clients SET full_name = :full_name, updated_at = NOW() WHERE id = :id');
        $stmt->execute([
            'id' => $id,
            'full_name' => $fullName,
        ]);
    }

    public static function searchByNameOrPhone(string $query, int $limit = 50): array
    {
        $phoneExact = normalize_phone($query);
        $stmt = self::db()->prepare(
            'SELECT c.id,
                    c.full_name,
                    c.phone,
                    v.id AS active_visit_id,
                    v.check_in_at AS active_check_in_at
             FROM clients c
             LEFT JOIN visits v
                ON v.client_id = c.id
               AND v.status = "active"
             WHERE c.phone = :phone_exact
                OR c.full_name LIKE :name_like
             ORDER BY (v.id IS NOT NULL) DESC, c.full_name ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':phone_exact', $phoneExact);
        $stmt->bindValue(':name_like', '%' . $query . '%');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }
}
