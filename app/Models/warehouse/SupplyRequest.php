<?php

namespace App\Models\warehouse;

use App\Models\fixedasset\OrganizationalUnit;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class SupplyRequest extends Model
{
    use HasFactory;

    protected $table = 'wh_supply_request';

    protected $fillable = [
        'date',
        'delivery_date',
        'observation',
        'requester_file',
        'approver_file',
        'warehouse_manager_file',
        'requester_id',
        'fa_organizational_unit_id',
        'immediate_boss_id',
        'delivered_by_id',
        'approved_by_id',
        'rejected_by_id',
        'status_id',
    ];

    protected $casts = [
        'request_date' => 'datetime',
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

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function organizationalUnit()
    {
        return $this->belongsTo(OrganizationalUnit::class, 'fa_organizational_unit_id');
    }

    public function immediateBoss()
    {
        return $this->belongsTo(User::class, 'immediate_boss_id');
    }

    public function details()
    {
        return $this->hasMany(SupplyRequestDetail::class, 'supply_request_id');
    }
}
