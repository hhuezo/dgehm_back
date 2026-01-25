<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class AssetClass extends Model
{
    protected $table = 'fa_classes';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'code',
        'useful_life',
        'fa_specific_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'useful_life' => 'integer',
    ];

    public function specific()
    {
        return $this->belongsTo(Specific::class, 'fa_specific_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('classes')
            ->logAll()
            ->logOnlyDirty();
    }
}
