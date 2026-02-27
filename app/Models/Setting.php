<?php

declare(strict_types=1);

namespace App\Models;

final class Setting extends BaseModel
{
    public static function get(string $key, mixed $default = null): mixed
    {
        $stmt = self::db()->prepare('SELECT `value` FROM settings WHERE `key` = :key LIMIT 1');
        $stmt->execute(['key' => $key]);
        $value = $stmt->fetchColumn();

        return $value === false ? $default : $value;
    }

    public static function all(): array
    {
        $stmt = self::db()->query('SELECT `key`, `value` FROM settings');
        $rows = $stmt->fetchAll() ?: [];

        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['key']] = $row['value'];
        }

        return $settings;
    }

    public static function setMany(array $settings): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO settings (`key`, `value`, updated_at)
             VALUES (:key, :value, NOW())
             ON DUPLICATE KEY UPDATE
                `value` = VALUES(`value`),
                updated_at = NOW()'
        );

        foreach ($settings as $key => $value) {
            $stmt->execute([
                'key' => (string) $key,
                'value' => (string) $value,
            ]);
        }
    }

    public static function hourlyRate(): float
    {
        return (float) self::get('hourly_rate', '10.00');
    }

    public static function currency(): string
    {
        return (string) self::get('currency', 'USD');
    }
}
