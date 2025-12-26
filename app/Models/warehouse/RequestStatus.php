<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\warehouse\SupplyRequest;

class RequestStatus extends Model
{
    use HasFactory;

    protected $table = 'wh_request_status';

    protected $fillable = [
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function supplyRequests()
    {
        return $this->hasMany(SupplyRequest::class, 'status_id');
    }
}
