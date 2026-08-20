<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Loggable
{
    /**
     * Boot the trait to listen for Eloquent events.
     */
    protected static function bootLoggable()
    {
        static::created(function ($model) {
            $model->logActivity('created', null, $model->getAttributes());
        });

        static::updated(function ($model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getAttributes();
            
            // Only log if there are actual changes
            if (!empty($model->getChanges())) {
                $model->logActivity('updated', $oldValues, $newValues);
            }
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted', $model->getAttributes(), null);
        });
    }

    /**
     * Helper to insert into the activity_logs table.
     */
    protected function logActivity($action, $oldValues = null, $newValues = null)
    {
        // Don't log if we are seeding or running from console without a web request
        // unless we want to track system background jobs (optional)
        $userId = Auth::id();

        // Optionally filter out hidden attributes like passwords
        if ($oldValues) {
            $oldValues = $this->filterLogAttributes($oldValues);
        }
        if ($newValues) {
            $newValues = $this->filterLogAttributes($newValues);
        }

        ActivityLog::create([
            'user_id'    => $userId,
            'action'     => $action,
            'model_type' => get_class($this),
            'model_id'   => $this->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Filter out hidden attributes from logging.
     */
    protected function filterLogAttributes(array $attributes)
    {
        $hidden = $this->getHidden();
        foreach ($hidden as $key) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = '[HIDDEN]';
            }
        }
        return $attributes;
    }
}
