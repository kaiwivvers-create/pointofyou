<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    protected static function bootLogsActivity()
    {
        static::created(function ($model) {
            if (Auth::check()) {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'ip_address' => request()->ip(),
                    'action' => 'created',
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => null,
                    'new_values' => $model->toArray(),
                    'metadata' => null,
                    'occurred_at' => now(),
                ]);
            }
        });

        static::updated(function ($model) {
            if (Auth::check()) {
                $changes = $model->getChanges();
                $original = $model->getOriginal();
                
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'ip_address' => request()->ip(),
                    'action' => 'updated',
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => array_intersect_key($original, $changes),
                    'new_values' => $changes,
                    'metadata' => null,
                    'occurred_at' => now(),
                ]);
            }
        });

        static::deleted(function ($model) {
            if (Auth::check()) {
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'ip_address' => request()->ip(),
                    'action' => 'deleted',
                    'model_type' => get_class($model),
                    'model_id' => $model->id,
                    'old_values' => $model->toArray(),
                    'new_values' => null,
                    'metadata' => null,
                    'occurred_at' => now(),
                ]);
            }
        });
    }
}
