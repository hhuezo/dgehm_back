<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdmDocumentType extends Model
{
    use SoftDeletes;

    protected $table = 'adm_document_types';

    protected $fillable = [
        'name',
        'format',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function employeeDocuments(): HasMany
    {
        return $this->hasMany(AdmEmployeeDocumentType::class, 'adm_document_type_id');
    }

    public function employees(): BelongsToMany
    {
        return $this->belongsToMany(Employee::class, 'adm_document_type_adm_employee', 'adm_document_type_id', 'adm_employee_id')
            ->using(AdmEmployeeDocumentType::class)
            ->withPivot(['id', 'value']);
    }
}
