<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class OrganizationalUnitType extends Model
{
    protected $table = 'fa_organizational_unit_types';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'staff',
        'is_active',
    ];

    protected $casts = [
        'staff' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('organizational_unit_types')
            ->logAll()
            ->logOnlyDirty();
    }
}
