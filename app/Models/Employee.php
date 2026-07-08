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
        'fixed_asset_manager',
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
            'fixed_asset_manager' => 'boolean',
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

    public function fixedAssetCategories(): BelongsToMany
    {
        return $this->belongsToMany(
            \App\Models\fixedasset\Category::class,
            'fa_category_employee',
            'adm_employee_id',
            'fa_category_id'
        )->withTimestamps();
    }

    public function resolveFaOrganizationalUnitId(): ?int
    {
        $principalAssignment = $this->functionalPositionAssignments()
            ->where('active', true)
            ->where('principal', true)
            ->with('functionalPosition.organizationalUnit')
            ->first();

        $admUnitName = $principalAssignment?->functionalPosition?->organizationalUnit?->name;

        if ($admUnitName) {
            $faUnitId = \App\Models\fixedasset\OrganizationalUnit::query()
                ->where('name', $admUnitName)
                ->value('id');

            if ($faUnitId) {
                return (int) $faUnitId;
            }
        }

        if ($this->user_id) {
            $faUnitId = $this->user()
                ->first()
                ?->organizationalUnits()
                ->value('fa_organizational_units.id');

            if ($faUnitId) {
                return (int) $faUnitId;
            }
        }

        return null;
    }
}
