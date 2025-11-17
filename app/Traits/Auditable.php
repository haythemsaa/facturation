<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait Auditable
{
    /**
     * Boot the trait.
     */
    protected static function bootAuditable(): void
    {
        static::created(function ($model) {
            $model->auditLog('created', null, $model->getAuditableAttributes());
        });

        static::updated(function ($model) {
            if ($model->isDirty()) {
                $model->auditLog('updated', $model->getOriginal(), $model->getAttributes());
            }
        });

        static::deleted(function ($model) {
            $event = $model->isForceDeleting() ? 'force_deleted' : 'deleted';
            $model->auditLog($event, $model->getAuditableAttributes(), null);
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function ($model) {
                $model->auditLog('restored', null, $model->getAuditableAttributes());
            });
        }
    }

    /**
     * Create audit log entry.
     */
    public function auditLog(string $event, ?array $oldValues, ?array $newValues): void
    {
        // Skip if no request (console commands, jobs, etc.)
        if (!request()) {
            return;
        }

        $description = $this->getAuditDescription($event);

        AuditLog::create([
            'tenant_id' => $this->tenant_id ?? auth()->user()?->tenant_id,
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'old_values' => $this->filterAuditableAttributes($oldValues),
            'new_values' => $this->filterAuditableAttributes($newValues),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'description' => $description,
        ]);
    }

    /**
     * Get audit logs for this model.
     */
    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    /**
     * Get auditable attributes (excluding sensitive data).
     */
    protected function getAuditableAttributes(): array
    {
        return $this->getAttributes();
    }

    /**
     * Filter attributes to exclude from audit.
     */
    protected function filterAuditableAttributes(?array $attributes): ?array
    {
        if (!$attributes) {
            return null;
        }

        $excluded = $this->getAuditExclude();

        return array_diff_key($attributes, array_flip($excluded));
    }

    /**
     * Get attributes to exclude from audit.
     */
    protected function getAuditExclude(): array
    {
        return array_merge(
            ['password', 'remember_token'],
            $this->auditExclude ?? []
        );
    }

    /**
     * Get human-readable description of the audit event.
     */
    protected function getAuditDescription(string $event): string
    {
        $modelName = class_basename($this);
        $identifier = $this->getAuditIdentifier();

        return match($event) {
            'created' => "{$modelName} '{$identifier}' créé",
            'updated' => "{$modelName} '{$identifier}' modifié",
            'deleted' => "{$modelName} '{$identifier}' supprimé",
            'force_deleted' => "{$modelName} '{$identifier}' supprimé définitivement",
            'restored' => "{$modelName} '{$identifier}' restauré",
            default => "{$modelName} '{$identifier}' - {$event}",
        };
    }

    /**
     * Get identifier for audit description.
     */
    protected function getAuditIdentifier(): string
    {
        return $this->name
            ?? $this->title
            ?? $this->code
            ?? $this->number
            ?? "#{$this->id}";
    }
}
