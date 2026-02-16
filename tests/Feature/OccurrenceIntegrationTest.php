<?php

namespace Tests\Feature;

use App\Models\EventInbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccurrenceIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_prevents_duplicate_occurrences_with_same_idempotency_key()
    {
        $payload = [
            'externalId' => 'EXT-123',
            'type' => 'incendio_urbano',
            'description' => 'Fogo em residência',
            'reportedAt' => now()->toIso8601String(),
        ];

        $headers = [
            'X-API-Key' => config('app.api_key'),
            'Idempotency-Key' => 'unique-key-123'
        ];

        $response1 = $this->postJson('/api/integrations/occurrences', $payload, $headers);
        $response1->assertStatus(202);

        $response2 = $this->postJson('/api/integrations/occurrences', $payload, $headers);
        $response2->assertStatus(200);
        
        $this->assertEquals(1, EventInbox::count());
    }

}
