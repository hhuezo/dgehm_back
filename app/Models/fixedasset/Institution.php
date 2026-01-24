<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Institution extends Model
{
    protected $table = 'fa_institutions';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'code',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('institutions')
            ->logAll()
            ->logOnlyDirty();
    }
}
