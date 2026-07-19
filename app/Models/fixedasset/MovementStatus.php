<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovementStatus extends Model
{
    public const PENDING_APPROVAL = 1;
    public const APPROVED = 2;
    public const REJECTED = 3;
    public const FINALIZED = 4;
    public const ANNULLED = 5;

    protected $table = 'fa_movement_statuses';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'status_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(Transfer::class, 'status_id');
    }
}
