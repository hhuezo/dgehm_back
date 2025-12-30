<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kardex extends Model
{
   protected $table = 'wh_kardex';

    protected $fillable = [
        'purchase_order_id',
        'product_id',
        'movement_type',
        'quantity',
        'unit_price',
        'subtotal',
        'supply_request_id',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

     public function supplierRequest(): BelongsTo
    {
        return $this->belongsTo(SupplyRequest::class, 'supply_request_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
