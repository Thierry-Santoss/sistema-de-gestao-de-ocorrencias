<?php

namespace App\Services;

use App\Models\EventInbox;
use App\Jobs\ProcessExternalOccurrence;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OccurrenceIntegrationService
{
  public function handle(array $data, string $idempotencyKey): string
  {
    $existingEvent = EventInbox::where('idempotency_key', $idempotencyKey)->first();

    if ($existingEvent) {
      return $existingEvent->id;
    }

    return DB::transaction(function () use ($data, $idempotencyKey) {
      $event = EventInbox::create([
        'id' => Str::uuid(),
        'idempotency_key' => $idempotencyKey,
        'external_id' => $data['externalId'],
        'source' => 'sistema_externo',
        'type' => 'occurrence.created',
        'payload' => $data,
        'status' => 'pending',
      ]);

      ProcessExternalOccurrence::dispatch($event);

      return $event->id;
    });
  }
}
