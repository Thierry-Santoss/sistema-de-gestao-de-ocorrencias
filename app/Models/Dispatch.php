<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class Dispatch extends Model
{
    use HasUuids, HasFactory;

    public const STATUS_ASSIGNED = 'assigned';
    public const STATUS_EN_ROUTE = 'en_route';
    public const STATUS_ON_SITE = 'on_site';
    public const STATUS_CLOSED = 'closed';

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
            self::STATUS_ASSIGNED => [self::STATUS_EN_ROUTE, self::STATUS_CLOSED],
            self::STATUS_EN_ROUTE => [self::STATUS_ON_SITE, self::STATUS_CLOSED],
            self::STATUS_ON_SITE  => [self::STATUS_CLOSED],
            self::STATUS_CLOSED   => [],
        ];

        if (!in_array($newStatus, $flow[$this->status] ?? [])) {
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
