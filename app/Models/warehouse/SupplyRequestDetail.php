<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;

class SupplyRequestDetail extends Model
{
    protected $table = 'wh_supply_request_detail';

    protected $fillable = [
        'supply_request_id',
        'product_id',
        'quantity',
        'delivered_quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'delivered_quantity' => 'decimal:2',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }

    public function supplyRequest(): BelongsTo
    {
        return $this->belongsTo(SupplyRequest::class, 'supply_request_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
