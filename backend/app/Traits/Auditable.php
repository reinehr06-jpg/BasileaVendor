<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    protected function logAudit($action)
    {
        $oldValues = [];
        $newValues = [];

        if ($action === 'updated') {
            $oldValues = $this->getOriginal();
            $newValues = $this->getChanges();
        } elseif ($action === 'created') {
            $newValues = $this->getAttributes();
        } elseif ($action === 'deleted') {
            $oldValues = $this->getAttributes();
        }

        // Não audita se nada mudou em updates
        if ($action === 'updated' && empty($newValues)) {
            return;
        }

        AuditLog::create([
            'user_id' => Auth::id(), // Pode ser null se disparado via CRON
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
