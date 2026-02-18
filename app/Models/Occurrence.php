<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;

class Occurrence extends Model
{
    use HasUuids, HasFactory;

    public const STATUS_REPORTED = 'reported';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CANCELLED = 'cancelled';

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
            self::STATUS_REPORTED => [self::STATUS_IN_PROGRESS, self::STATUS_CANCELLED],
            self::STATUS_IN_PROGRESS => [self::STATUS_RESOLVED, self::STATUS_CANCELLED],
            self::STATUS_RESOLVED => [],
            self::STATUS_CANCELLED => [],
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

    protected static function booted()
    {
        static::saved(function () {
            Cache::tags(['occurrences'])->flush();
        });
    }
}
