<?php

namespace App\Models\warehouse;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Product extends Model
{
    use HasFactory;

    protected $table = 'wh_products';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'accounting_account_id',
        'measure_id',
        'name',
        'description',
        'minimo',
        'maximo',
        'is_active',
        'environmental_report',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'environmental_report' => 'boolean',
        'minimo' => 'integer',
        'maximo' => 'integer',
    ];

    public function accountingAccount()
    {
        return $this->belongsTo(AccountingAccount::class, 'accounting_account_id');
    }

    public function measure()
    {
        return $this->belongsTo(Measure::class, 'measure_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }
}
