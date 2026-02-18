<?php

namespace App\Services;

use App\Models\EventInbox;
use App\Jobs\ProcessExternalOccurrence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OccurrenceIntegrationService
{
    public function handle(array $data, string $idempotencyKey): EventInbox
    {
        $existingEvent = EventInbox::where('idempotency_key', $idempotencyKey)->first();

        if ($existingEvent) {
            return $existingEvent;
        }

        return DB::transaction(function () use ($data, $idempotencyKey) {
            $newEvent = EventInbox::create([
                'id' => Str::uuid(),
                'idempotency_key' => $idempotencyKey,
                'external_id' => $data['externalId'],
                'source' => 'sistema_externo',
                'type' => 'occurrence.created',
                'payload' => $data,
                'status' => 'pending',
            ]);

            ProcessExternalOccurrence::dispatch($newEvent);

            return $newEvent;
        });
    }
}
