<?php

namespace Tests\Feature;

use App\Models\Occurrence;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OccurrenceStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_audit_log_on_status_change()
    {
        $occurrence = Occurrence::factory()->create(['status' => 'reported']);
        
        $headers = ['X-API-Key' => config('app.api_key')];

        $this->postJson("/api/occurrences/{$occurrence->id}/start", [], $headers)
             ->assertStatus(200);

        
        $this->assertDatabaseHas('audit_logs', [
            'entity_type' => 'Occurrence',
            'entity_id' => $occurrence->id,
            'action' => 'status_changed'
        ]);
    }

    public function test_it_prevents_invalid_status_transition()
    {
        
        $occurrence = Occurrence::factory()->create(['status' => 'reported']);
        
        $headers = ['X-API-Key' => config('app.api_key')];

        $this->postJson("/api/occurrences/{$occurrence->id}/resolve", [], $headers)
             ->assertStatus(422);
    }
}
