<?php

declare(strict_types=1);

namespace App\Models;

final class Payment extends BaseModel
{
    public static function create(int $visitId, string $method, float $amountPaid, int $receivedBy): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO payments (visit_id, method, amount_paid, paid_at, received_by, created_at)
             VALUES (:visit_id, :method, :amount_paid, NOW(), :received_by, NOW())'
        );
        $stmt->execute([
            'visit_id' => $visitId,
            'method' => $method,
            'amount_paid' => $amountPaid,
            'received_by' => $receivedBy,
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function existsForVisit(int $visitId): bool
    {
        $stmt = self::db()->prepare('SELECT 1 FROM payments WHERE visit_id = :visit_id LIMIT 1');
        $stmt->execute(['visit_id' => $visitId]);
        return (bool) $stmt->fetchColumn();
    }

    public static function findReceiptByPaymentId(int $paymentId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT p.id AS payment_id,
                    p.method,
                    p.amount_paid,
                    p.paid_at,
                    v.id AS visit_id,
                    v.check_in_at,
                    v.check_out_at,
                    v.duration_minutes,
                    v.billable_hours,
                    v.hourly_rate_snapshot,
                    v.time_charge,
                    v.addons_total,
                    v.grand_total,
                    c.full_name,
                    c.phone,
                    u.name AS received_by_name
             FROM payments p
             INNER JOIN visits v ON v.id = p.visit_id
             INNER JOIN clients c ON c.id = v.client_id
             INNER JOIN users u ON u.id = p.received_by
             WHERE p.id = :payment_id
             LIMIT 1'
        );
        $stmt->execute(['payment_id' => $paymentId]);

        $row = $stmt->fetch();
        return $row ?: null;
    }
}
