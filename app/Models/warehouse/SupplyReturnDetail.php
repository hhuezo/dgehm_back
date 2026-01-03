<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\warehouse\SupplyReturn;
use App\Models\warehouse\Product;

class SupplyReturnDetail extends Model
{
    use HasFactory;

    protected $table = 'wh_supply_returns_detail';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $keyType = 'int';

    protected $fillable = [
        'supply_return_id',
        'product_id',
        'returned_quantity',
        'observation',
        'supply_return_id'
    ];

    protected $casts = [
        'returned_quantity' => 'integer',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];


    public function supplyReturn()
    {
        return $this->belongsTo(SupplyReturn::class, 'supply_return_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
