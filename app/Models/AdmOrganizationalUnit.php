<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmOrganizationalUnit extends Model
{
    use SoftDeletes;

    protected $table = 'adm_organizational_units';

    protected $fillable = [
        'name',
        'abbreviation',
        'code',
        'active',
        'adm_organizational_unit_type_id',
        'adm_organizational_unit_id',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AdmOrganizationalUnitType::class, 'adm_organizational_unit_type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'adm_organizational_unit_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'adm_organizational_unit_id');
    }

    public function functionalPositions(): HasMany
    {
        return $this->hasMany(AdmFunctionalPosition::class, 'adm_organizational_unit_id');
    }
}
