<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Device extends Model
{
    protected $fillable = [
        'user_id',
        'device_uuid',
        'name',
        'platform',
        'push_token',
        'ip',
        'user_agent',
        'revoked',
        'last_active_at',
    ];

    protected $casts = [
        'revoked' => 'boolean',
        'last_active_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
