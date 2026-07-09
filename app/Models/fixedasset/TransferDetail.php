<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;

class TransferDetail extends Model
{
    protected $table = 'fa_transfer_details';

    protected $fillable = [
        'fa_transfer_id',
        'fa_fixed_asset_id',
        'observation',
    ];

    public function transfer(): BelongsTo
    {
        return $this->belongsTo(Transfer::class, 'fa_transfer_id');
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fa_fixed_asset_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fa_transfer_details')
            ->logAll()
            ->logOnlyDirty();
    }
}
