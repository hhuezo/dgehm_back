<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $table = 'adm_employees';

    protected $fillable = [
        'name',
        'lastname',
        'email',
        'email_personal',
        'phone',
        'phone_personal',
        'photo_name',
        'photo_route',
        'photo_route_sm',
        'birthday',
        'marking_required',
        'status',
        'active',
        'user_id',
        'adm_gender_id',
        'adm_marital_status_id',
        'remote_mark',
        'external',
        'viatic',
        'children',
        'unsubscribe_justification',
        'vehicle',
        'adhonorem',
        'parking',
        'disabled',
        'warehouse_manager',
    ];

    protected function casts(): array
    {
        return [
            'birthday' => 'date',
            'marking_required' => 'boolean',
            'active' => 'boolean',
            'remote_mark' => 'boolean',
            'external' => 'boolean',
            'viatic' => 'boolean',
            'children' => 'boolean',
            'vehicle' => 'boolean',
            'adhonorem' => 'boolean',
            'parking' => 'boolean',
            'disabled' => 'boolean',
            'warehouse_manager' => 'boolean',
            'status' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gender(): BelongsTo
    {
        return $this->belongsTo(AdmGender::class, 'adm_gender_id');
    }

    public function maritalStatus(): BelongsTo
    {
        return $this->belongsTo(AdmMaritalStatus::class, 'adm_marital_status_id');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AdmEmployeeDocumentType::class, 'adm_employee_id');
    }

    public function documentTypes(): BelongsToMany
    {
        return $this->belongsToMany(AdmDocumentType::class, 'adm_document_type_adm_employee', 'adm_employee_id', 'adm_document_type_id')
            ->using(AdmEmployeeDocumentType::class)
            ->withPivot(['id', 'value']);
    }

    public function functionalPositionAssignments(): HasMany
    {
        return $this->hasMany(AdmEmployeeFunctionalPosition::class, 'adm_employee_id');
    }

    public function functionalPositions(): BelongsToMany
    {
        return $this->belongsToMany(AdmFunctionalPosition::class, 'adm_employee_adm_functional_position', 'adm_employee_id', 'adm_functional_position_id')
            ->using(AdmEmployeeFunctionalPosition::class)
            ->withPivot(['id', 'date_start', 'date_end', 'principal', 'salary', 'active'])
            ->withTimestamps();
    }
}
