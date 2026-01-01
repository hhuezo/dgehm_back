<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\warehouse\RequestStatus;

class SupplyRequest extends Model
{
    use HasFactory;

    protected $table = 'wh_supply_request';

    protected $fillable = [
        'request_date',
        'observation',
        'requester_id',
        'office_id',
        'immediate_boss',
        'delivered_by',
        'status_id',
    ];

    protected $casts = [
        'request_date' => 'datetime',
    ];

    public function status()
    {
        return $this->belongsTo(RequestStatus::class, 'status_id');
    }

    public function requester()
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
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
