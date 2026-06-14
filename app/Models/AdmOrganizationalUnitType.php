<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmOrganizationalUnitType extends Model
{
    use SoftDeletes;

    protected $table = 'adm_organizational_unit_types';

    protected $fillable = [
        'name',
        'staff',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'staff' => 'boolean',
            'active' => 'boolean',
        ];
    }

    public function organizationalUnits(): HasMany
    {
        return $this->hasMany(AdmOrganizationalUnit::class, 'adm_organizational_unit_type_id');
    }
}
