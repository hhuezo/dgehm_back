<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class FixedAsset extends Model
{
    protected $table = 'fa_fixed_assets';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'fa_class_id',
        'code',
        'correlative',
        'description',
        'brand',
        'model',
        'serial_number',
        'location',
        'policy',
        'current_responsible',
        'organizational_unit_id',
        'asset_type',
        'acquisition_date',
        'supplier',
        'invoice',
        'origin_id',
        'physical_condition_id',
        'additional_description',
        'measurements',
        'observation',
        'is_insured',
        'insured_description',
        'purchase_value',
    ];

    protected $casts = [
        'acquisition_date' => 'date',
        'is_insured' => 'boolean',
        'purchase_value' => 'decimal:2',
    ];

    public function assetClass()
    {
        return $this->belongsTo(AssetClass::class, 'fa_class_id');
    }

    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    public function physicalCondition()
    {
        return $this->belongsTo(PhysicalCondition::class, 'physical_condition_id');
    }

    public function origin()
    {
        return $this->belongsTo(Origin::class, 'origin_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fixed_assets')
            ->logAll()
            ->logOnlyDirty();
    }
}

