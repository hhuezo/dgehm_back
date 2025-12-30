<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\warehouse\Office;

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
        'wh_office_id',
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

    public function office()
    {
        return $this->belongsTo(Office::class, 'wh_office_id');
    }
}
