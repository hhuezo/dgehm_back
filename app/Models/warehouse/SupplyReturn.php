<?php

namespace App\Models\warehouse;

use App\Models\fixedasset\OrganizationalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class SupplyReturn extends Model
{
    use HasFactory;

    protected $table = 'wh_supply_returns';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'return_date',
        'returned_by_id',
        'fa_organizational_unit_id',
        'immediate_supervisor_id',
        'received_by_id',
        'phone_extension',
        'general_observations',
    ];

    protected $casts = [
        'return_date' => 'date',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }

    public function status()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function returnedBy()
    {
        return $this->belongsTo(User::class, 'returned_by_id');
    }

    public function immediateSupervisor()
    {
        return $this->belongsTo(User::class, 'immediate_supervisor_id');
    }

    public function receivedBy()
    {
        return $this->belongsTo(User::class, 'received_by_id');
    }

    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'fa_organizational_unit_id');
    }

    public function details()
    {
        return $this->hasMany(SupplyReturnDetail::class, 'supply_return_id');
    }
}
