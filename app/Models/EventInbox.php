<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventInbox extends Model
{
    use HasUuids, HasFactory;

    protected $table = 'event_inbox';

    protected $fillable = [
        'idempotency_key',
        'source',
        'type',
        'payload',
        'status',
        'error',
        'processed_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'processed_at' => 'datetime'
    ];
}
