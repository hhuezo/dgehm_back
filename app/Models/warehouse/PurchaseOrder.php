<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_order';
    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'supplier_id',
        'order_number',
        'budget_commitment_number',
        'acta_date',
        'reception_time',
        'supplier_representative',
        'invoice_number',
        'invoice_date',
        'total_amount',
        'administrative_manager',
        'administrative_technician',
    ];

    protected $casts = [
        'acta_date' => 'date',
        'reception_time' => 'datetime',
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }
}
