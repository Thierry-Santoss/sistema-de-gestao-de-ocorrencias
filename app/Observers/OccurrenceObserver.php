<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\Occurrence;

class OccurrenceObserver
{
    /**
     * Handle the Occurrence "created" event.
     */
    public function created(Occurrence $occurrence): void
    {
        //
    }

    /**
     * Handle the Occurrence "updated" event.
     */
    public function updated(Occurrence $occurrence): void
    {
        if ($occurrence->wasChanged('status')) {
            AuditLog::create([
                'entity_type' => 'Occurrence',
                'entity_id'   => $occurrence->id,
                'action'      => 'status_changed',
                'before'      => ['status' => $occurrence->getOriginal('status')],
                'after'       => ['status' => $occurrence->status],
                'meta'        => [
                    'source'     => request()->header('X-API-Key') ? 'api' : 'system',
                    'ip'         => request()->ip(),
                    'user_agent' => request()->userAgent()
                ]
            ]);
        }
    }

    /**
     * Handle the Occurrence "deleted" event.
     */
    public function deleted(Occurrence $occurrence): void
    {
        //
    }

    /**
     * Handle the Occurrence "restored" event.
     */
    public function restored(Occurrence $occurrence): void
    {
        //
    }

    /**
     * Handle the Occurrence "force deleted" event.
     */
    public function forceDeleted(Occurrence $occurrence): void
    {
        //
    }
}
