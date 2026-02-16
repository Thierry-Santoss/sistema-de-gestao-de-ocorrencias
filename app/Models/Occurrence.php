<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Occurrence extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'external_id',
        'type',
        'status',
        'description',
        'reported_at'
    ];

    protected $casts = [
        'reported_at' => 'datetime',
    ];

    public function dispatches(): HasMany
    {
        return $this->hasMany(Dispatch::class);
    }

    public function canTransitionTo(string $newStatus): bool
    {
        $transitions = [
            'reported'    => ['in_progress', 'cancelled'],
            'in_progress' => ['resolved', 'cancelled'],
            'resolved'    => [],
            'cancelled'   => [],
        ];

        return in_array($newStatus, $transitions[$this->status] ?? []);
    }

    public function transitionTo(string $newStatus): bool
    {
        if (!$this->canTransitionTo($newStatus)) {
            return false;
        }

        return $this->update(['status' => $newStatus]);
    }
}
