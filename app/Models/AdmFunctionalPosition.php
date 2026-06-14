<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmFunctionalPosition extends Model
{
    use SoftDeletes;

    protected $table = 'adm_functional_positions';

    protected $fillable = [
        'name',
        'abbreviation',
        'description',
        'amount_required',
        'salary_min',
        'salary_max',
        'boss',
        'boss_hierarchy',
        'original',
        'user_required',
        'active',
        'adm_organizational_unit_id',
        'adm_functional_position_id',
    ];

    protected function casts(): array
    {
        return [
            'amount_required' => 'integer',
            'salary_min' => 'decimal:2',
            'salary_max' => 'decimal:2',
            'boss' => 'boolean',
            'boss_hierarchy' => 'integer',
            'original' => 'integer',
            'user_required' => 'integer',
            'active' => 'boolean',
        ];
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(AdmOrganizationalUnit::class, 'adm_organizational_unit_id');
    }

    public function parentPosition(): BelongsTo
    {
        return $this->belongsTo(self::class, 'adm_functional_position_id');
    }

    public function childPositions(): HasMany
    {
        return $this->hasMany(self::class, 'adm_functional_position_id');
    }

    public function employeeAssignments(): HasMany
    {
        return $this->hasMany(AdmEmployeeFunctionalPosition::class, 'adm_functional_position_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'adm_employee_adm_functional_position', 'adm_functional_position_id', 'adm_employee_id')
            ->using(AdmEmployeeFunctionalPosition::class)
            ->withPivot(['id', 'date_start', 'date_end', 'principal', 'salary', 'active'])
            ->withTimestamps();
    }
}
