<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    /**
     * Votes only record created_at — no updated_at needed.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'poll_id',
        'option_id',
        'ip_address',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function poll(): BelongsTo
    {
        return $this->belongsTo(Poll::class);
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(Option::class);
    }
}
