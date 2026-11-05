<?php

namespace App\Support\Installations;

use App\Models\AuditLog;
use App\Models\Installation;

final class InstallationAudit
{
    /**
     * @param  array<string, mixed>|null  $before
     */
    public static function record(?int $userId, string $action, Installation $installation, ?array $before, ?string $note = null): void
    {
        $after = $installation->only(['client_id', 'name', 'address', 'city', 'department', 'archived_at']);
        $note = trim((string) $note);
        if ($note !== '') {
            $after['note'] = $note;
        }

        AuditLog::query()->create([
            'user_id' => $userId,
            'action' => $action,
            'auditable_type' => Installation::class,
            'auditable_id' => $installation->id,
            'before' => $before,
            'after' => $after,
        ]);
    }

    public static function movement(?int $userId, Installation $installation, string $detail): void
    {
        self::record($userId, 'installation_post_movement', $installation, null, $detail);
    }
}
