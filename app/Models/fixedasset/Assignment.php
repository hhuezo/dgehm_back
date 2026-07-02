<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;

class Assignment extends Model
{
    protected $table = 'fa_assignments';

    protected $fillable = [
        'is_assignment',
        'is_unassignment',
        'date',
        'is_permanent',
        'temporal_start_date',
        'temporal_end_date',
        'organizational_unit_id',
        'person_id',
        'collaborator_id',
        'observation',
    ];

    protected $casts = [
        'is_assignment' => 'boolean',
        'is_unassignment' => 'boolean',
        'date' => 'date',
        'is_permanent' => 'boolean',
        'temporal_start_date' => 'date',
        'temporal_end_date' => 'date',
    ];

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
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
