<?php

namespace App\Jobs;

use App\Models\EventInbox;
use App\Models\Occurrence;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessExternalOccurrence implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [10, 30, 60];

    public $timeout = 60;

    public function __construct(
        public EventInbox $event
    ) {
    }

    public function handle(): void
    {
        $lockKey = 'process_event_' . $this->event->id;
        $lock = Cache::lock($lockKey, 10);

        if (!$lock->get()) {
            return;
        }

        try {
            if ($this->event->status === 'processed') {
                return;
            }

            DB::transaction(function () {
                $payload = $this->event->payload;

                Occurrence::updateOrCreate(
                    ['external_id' => $payload['externalId']],
                    [
                        'type' => $payload['type'],
                        'description' => $payload['description'],
                        'reported_at' => $payload['reportedAt'],
                        'status' => Occurrence::STATUS_REPORTED,
                    ]
                );

                $this->event->update([
                    'status' => 'processed',
                    'processed_at' => now(),
                ]);

                Log::info("Evento {$this->event->id} processado com sucesso.");
            });

        } catch (\Throwable $e) {
            $this->event->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);

            Log::error("Erro ao processar evento {$this->event->id}: " . $e->getMessage());

            throw $e;
        } finally {
            $lock->release();
        }
    }
}
