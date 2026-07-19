<?php

namespace App\Models\fixedasset;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;

class Assignment extends Model
{
    protected $table = 'fa_assignments';

    protected $fillable = [
        'date',
        'organizational_unit_id',
        'person_id',
        'observation',
        'reception_act_file',
        'annulment_reason',
        'status_id',
    ];

    protected $casts = [
        'date' => 'date',
        'status_id' => 'integer',
    ];

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    public function person(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'person_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(MovementStatus::class, 'status_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(AssignmentDetail::class, 'fa_assignment_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fa_assignments')
            ->logAll()
            ->logOnlyDirty();
    }
}
