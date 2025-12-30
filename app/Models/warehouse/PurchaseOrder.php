<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use App\Models\User;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'wh_purchase_order';
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
        'administrative_technician_id',
    ];

    protected $casts = [
        'acta_date' => 'datetime',
        'reception_time' => 'datetime',
        'invoice_date' => 'date',
        'total_amount' => 'decimal:2',
    ];


    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function administrativeTechnician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'administrative_technician_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(Kardex::class, 'purchase_order_id');
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }
}
