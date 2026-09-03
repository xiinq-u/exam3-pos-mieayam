<?php

namespace App;

use App\Models\AuditLog;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AuditLogger
{
    /**
     * @param  array<string, mixed>  $properties
     */
    public static function record(?Authenticatable $user, string $action, Model $model, array $properties = []): void
    {
        AuditLog::create([
            'user_id' => $user?->getAuthIdentifier(),
            'action' => $action,
            'auditable_type' => $model::class,
            'auditable_id' => $model->getKey(),
            'properties' => $properties,
        ]);
    }
}
