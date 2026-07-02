<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;

class AssignmentDetail extends Model
{
    protected $table = 'fa_assignment_details';

    protected $fillable = [
        'fa_assignment_id',
        'fa_fixed_asset_id',
        'observation',
    ];

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class, 'fa_assignment_id');
    }

    public function fixedAsset(): BelongsTo
    {
        return $this->belongsTo(FixedAsset::class, 'fa_fixed_asset_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fa_assignment_details')
            ->logAll()
            ->logOnlyDirty();
    }
}
