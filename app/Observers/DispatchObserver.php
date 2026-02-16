<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Dispatch;

class DispatchObserver
{
    /**
     * Handle the Dispatch "created" event.
     */
    public function created(Dispatch $dispatch): void
    {
        AuditLog::create([
            'entity_type' => 'Dispatch',
            'entity_id'   => $dispatch->id,
            'action'      => 'resource_dispatched',
            'before'      => null,
            'after'       => [
                'status' => $dispatch->status,
                'resource_code' => $dispatch->resource_code
            ],
            'meta'        => [
                'occurrence_id' => $dispatch->occurrence_id,
                'ip'            => request()->ip()
            ]
        ]);
    }

    /**
     * Handle the Dispatch "updated" event.
     */
    public function updated(Dispatch $dispatch): void
    {
        if ($dispatch->wasChanged('status')) {
            AuditLog::create([
                'entity_type' => 'Dispatch',
                'entity_id'   => $dispatch->id,
                'action'      => 'status_changed',
                'before'      => ['status' => $dispatch->getOriginal('status')],
                'after'       => ['status' => $dispatch->status],
                'meta'        => [
                    'resource_code' => $dispatch->resource_code,
                    'ip'            => request()->ip(),
                    'user_agent'    => request()->userAgent()
                ]
            ]);
        }
    }

    /**
     * Handle the Dispatch "deleted" event.
     */
    public function deleted(Dispatch $dispatch): void
    {
        //
    }

    /**
     * Handle the Dispatch "restored" event.
     */
    public function restored(Dispatch $dispatch): void
    {
        //
    }

    /**
     * Handle the Dispatch "force deleted" event.
     */
    public function forceDeleted(Dispatch $dispatch): void
    {
        //
    }
}
