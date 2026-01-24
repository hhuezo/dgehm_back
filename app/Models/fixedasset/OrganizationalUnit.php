<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class OrganizationalUnit extends Model
{
    protected $table = 'fa_organizational_units';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'abbreviation',
        'code',
        'is_active',
        'fa_organizational_unit_type_id',
        'fa_organizational_unit_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function type()
    {
        return $this->belongsTo(OrganizationalUnitType::class, 'fa_organizational_unit_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'fa_organizational_unit_id');
    }

    public function children()
    {
        return $this->hasMany(OrganizationalUnit::class, 'fa_organizational_unit_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('organizational_units')
            ->logAll()
            ->logOnlyDirty();
    }
}
