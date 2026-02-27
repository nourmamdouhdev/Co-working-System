<?php

declare(strict_types=1);

namespace App\Models;

final class AuditLog extends BaseModel
{
    public static function log(int $actorId, string $action, string $entity, ?int $entityId = null, ?array $payload = null): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO audit_logs (actor_id, action, entity, entity_id, payload, created_at)
             VALUES (:actor_id, :action, :entity, :entity_id, :payload, NOW())'
        );
        $stmt->execute([
            'actor_id' => $actorId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'payload' => $payload ? json_encode($payload, JSON_THROW_ON_ERROR) : null,
        ]);
    }
}
