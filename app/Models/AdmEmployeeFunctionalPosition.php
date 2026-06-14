<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdmEmployeeFunctionalPosition extends Pivot
{
    public $incrementing = true;

    protected $table = 'adm_employee_adm_functional_position';

    protected $fillable = [
        'date_start',
        'date_end',
        'principal',
        'salary',
        'active',
        'adm_employee_id',
        'adm_functional_position_id',
    ];

    protected function casts(): array
    {
        return [
            'date_start' => 'date',
            'date_end' => 'date',
            'principal' => 'boolean',
            'salary' => 'decimal:2',
            'active' => 'boolean',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'adm_employee_id');
    }

    public function functionalPosition(): BelongsTo
    {
        return $this->belongsTo(AdmFunctionalPosition::class, 'adm_functional_position_id');
    }
}
