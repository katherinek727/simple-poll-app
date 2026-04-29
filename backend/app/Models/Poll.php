<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Poll extends Model
{
    /**
     * Disable updated_at — polls table only has created_at.
     */
    public const UPDATED_AT = null;

    protected $fillable = [
        'title',
        'short_code',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function options(): HasMany
    {
        return $this->hasMany(Option::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }
}
