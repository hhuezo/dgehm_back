<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DepreciationStatus extends Model
{
    public const PENDING_ASSIGNMENT = 1;
    public const ACTIVE = 2;
    public const WAITING = 3;
    public const DISPOSED = 4;

    protected $table = 'fa_depreciation_statuses';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function fixedAssets(): HasMany
    {
        return $this->hasMany(FixedAsset::class, 'depreciation_status_id');
    }
}
