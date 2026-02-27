<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;

final class Visit extends BaseModel
{
    public static function findActiveByClient(int $clientId): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM visits WHERE client_id = :client_id AND status = "active" LIMIT 1');
        $stmt->execute(['client_id' => $clientId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function createActive(int $clientId, int $staffUserId, float $hourlyRate): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO visits (
                client_id,
                checked_in_by,
                check_in_at,
                status,
                hourly_rate_snapshot,
                duration_minutes,
                billable_hours,
                time_charge,
                addons_total,
                grand_total,
                created_at,
                updated_at
            ) VALUES (
                :client_id,
                :checked_in_by,
                NOW(),
                "active",
                :hourly_rate,
                0,
                0,
                0,
                0,
                0,
                NOW(),
                NOW()
            )'
        );
        $stmt->execute([
            'client_id' => $clientId,
            'checked_in_by' => $staffUserId,
            'hourly_rate' => $hourlyRate,
        ]);

        return (int) self::db()->lastInsertId();
    }

    public static function listActive(int $limit = 100): array
    {
        $stmt = self::db()->prepare(
            'SELECT v.*, c.full_name, c.phone, u.name AS checked_in_by_name
             FROM visits v
             INNER JOIN clients c ON c.id = v.client_id
             INNER JOIN users u ON u.id = v.checked_in_by
             WHERE v.status = "active"
             ORDER BY v.check_in_at ASC
             LIMIT :limit'
        );
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    public static function findByIdWithClient(int $visitId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT v.*, c.full_name, c.phone
             FROM visits v
             INNER JOIN clients c ON c.id = v.client_id
             WHERE v.id = :id
             LIMIT 1'
        );
        $stmt->execute(['id' => $visitId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findActiveByIdForUpdate(int $visitId): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM visits WHERE id = :id AND status = "active" LIMIT 1 FOR UPDATE'
        );
        $stmt->execute(['id' => $visitId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function closeVisit(
        int $visitId,
        int $durationMinutes,
        int $billableHours,
        float $timeCharge,
        float $addonsTotal,
        float $grandTotal
    ): void {
        $stmt = self::db()->prepare(
            'UPDATE visits
             SET check_out_at = NOW(),
                 status = "closed",
                 duration_minutes = :duration_minutes,
                 billable_hours = :billable_hours,
                 time_charge = :time_charge,
                 addons_total = :addons_total,
                 grand_total = :grand_total,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'id' => $visitId,
            'duration_minutes' => $durationMinutes,
            'billable_hours' => $billableHours,
            'time_charge' => $timeCharge,
            'addons_total' => $addonsTotal,
            'grand_total' => $grandTotal,
        ]);
    }

    public static function addAddonLine(
        int $visitId,
        int $productId,
        string $productName,
        float $unitPrice,
        int $qty,
        float $lineTotal
    ): void {
        $stmt = self::db()->prepare(
            'INSERT INTO visit_addons (
                visit_id,
                product_id,
                product_name_snapshot,
                unit_price_snapshot,
                qty,
                line_total,
                created_at
            ) VALUES (
                :visit_id,
                :product_id,
                :product_name_snapshot,
                :unit_price_snapshot,
                :qty,
                :line_total,
                NOW()
            )'
        );
        $stmt->execute([
            'visit_id' => $visitId,
            'product_id' => $productId,
            'product_name_snapshot' => $productName,
            'unit_price_snapshot' => $unitPrice,
            'qty' => $qty,
            'line_total' => $lineTotal,
        ]);
    }

    public static function listAddons(int $visitId): array
    {
        $stmt = self::db()->prepare('SELECT * FROM visit_addons WHERE visit_id = :visit_id ORDER BY id ASC');
        $stmt->execute(['visit_id' => $visitId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function computeDurationMinutes(string $checkInAt, ?string $checkOutAt = null): int
    {
        $start = new DateTimeImmutable($checkInAt);
        $end = $checkOutAt ? new DateTimeImmutable($checkOutAt) : new DateTimeImmutable('now');
        $seconds = max(0, $end->getTimestamp() - $start->getTimestamp());
        return (int) floor($seconds / 60);
    }

    public static function listClosedByDate(string $date): array
    {
        $stmt = self::db()->prepare(
            'SELECT v.id,
                    c.full_name,
                    c.phone,
                    v.check_in_at,
                    v.check_out_at,
                    v.billable_hours,
                    v.grand_total,
                    p.method AS payment_method
             FROM visits v
             INNER JOIN clients c ON c.id = v.client_id
             LEFT JOIN payments p ON p.visit_id = v.id
             WHERE v.status = "closed"
               AND DATE(v.check_out_at) = :report_date
             ORDER BY v.check_out_at DESC'
        );
        $stmt->execute(['report_date' => $date]);

        return $stmt->fetchAll() ?: [];
    }

    public static function dailySummary(string $date): array
    {
        $db = self::db();

        $checkInsStmt = $db->prepare('SELECT COUNT(*) FROM visits WHERE DATE(check_in_at) = :d');
        $checkInsStmt->execute(['d' => $date]);

        $checkOutsStmt = $db->prepare('SELECT COUNT(*) FROM visits WHERE status = "closed" AND DATE(check_out_at) = :d');
        $checkOutsStmt->execute(['d' => $date]);

        $totalsStmt = $db->prepare(
            'SELECT
                COALESCE(SUM(v.billable_hours), 0) AS total_hours,
                COALESCE(SUM(v.grand_total), 0) AS total_revenue,
                COALESCE(SUM(CASE WHEN p.method = "cash" THEN p.amount_paid ELSE 0 END), 0) AS cash_revenue,
                COALESCE(SUM(CASE WHEN p.method = "visa" THEN p.amount_paid ELSE 0 END), 0) AS visa_revenue
             FROM visits v
             LEFT JOIN payments p ON p.visit_id = v.id
             WHERE v.status = "closed"
               AND DATE(v.check_out_at) = :d'
        );
        $totalsStmt->execute(['d' => $date]);

        return [
            'check_ins' => (int) $checkInsStmt->fetchColumn(),
            'check_outs' => (int) $checkOutsStmt->fetchColumn(),
            ...($totalsStmt->fetch() ?: [
                'total_hours' => 0,
                'total_revenue' => 0,
                'cash_revenue' => 0,
                'visa_revenue' => 0,
            ]),
        ];
    }
}
