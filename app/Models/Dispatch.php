<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dispatch extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'occurrence_id',
        'resource_code',
        'status'
    ];

    public function occurrence(): BelongsTo
    {
        return $this->belongsTo(Occurrence::class);
    }
    
    public function updateStatus(string $newStatus): bool
    {
        $flow = [
            'assigned' => ['en_route', 'closed'],
            'en_route' => ['on_site', 'closed'],
            'on_site'  => ['closed'],
            'closed'   => []
        ];

        if (!in_array($newStatus, $flow[$this->status] ?? [])) {
            return false;
        }

        return $this->update(['status' => $newStatus]);
    }
}
