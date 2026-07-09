<?php

namespace App\Models\fixedasset;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;

class Transfer extends Model
{
    public const STATUS_ENTERED = 1;
    public const STATUS_FINALIZED = 2;

    protected $table = 'fa_transfers';

    protected $fillable = [
        'date',
        'organizational_unit_id',
        'person_delivers_id',
        'person_receives_id',
        'observation',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'status' => 'integer',
    ];

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class, 'organizational_unit_id');
    }

    public function personDelivers(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'person_delivers_id');
    }

    public function personReceives(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'person_receives_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(TransferDetail::class, 'fa_transfer_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('fa_transfers')
            ->logAll()
            ->logOnlyDirty();
    }
}
