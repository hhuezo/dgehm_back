<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use App\Models\Employee;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'wh_purchase_order';
    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'supplier_id',
        'wh_funding_sources_id',
        'order_number',
        'budget_commitment_number',
        'acta_date',
        'reception_date',
        'supplier_representative',
        'invoice_number',
        'invoice_date',
        'purchase_order_administrator_id',
        'administrative_technician_id',
        'file',
        'partial_delivery',
    ];

    protected $casts = [
        'partial_delivery' => 'boolean',
    ];

    protected $appends = [
        'total_amount',
    ];




    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function fundingSource(): BelongsTo
    {
        return $this->belongsTo(FundingSource::class, 'wh_funding_sources_id');
    }

    public function purchaseOrderAdministrator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'purchase_order_administrator_id');
    }

    public function administrativeTechnician(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'administrative_technician_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(Kardex::class, 'purchase_order_id');
    }

    public function getTotalAmountAttribute(): float
    {
        if (array_key_exists('total_amount', $this->attributes) && $this->attributes['total_amount'] !== null) {
            return round((float) $this->attributes['total_amount'], 2);
        }

        if ($this->relationLoaded('details')) {
            return round((float) $this->details
                ->where('movement_type', 1)
                ->sum('subtotal'), 2);
        }

        return round((float) $this->details()
            ->where('movement_type', 1)
            ->sum('subtotal'), 2);
    }

    public function scopeWithDetailsTotal($query)
    {
        return $query->withSum(
            ['details as total_amount' => fn ($q) => $q->where('movement_type', 1)],
            'subtotal'
        );
    }


    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('purchase_orders')
            ->logAll()
            ->logOnlyDirty();
    }
}
