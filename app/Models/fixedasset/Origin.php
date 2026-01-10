<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Origin extends Model
{
      protected $table = 'fa_origins';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',

        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('functional_positions')
            ->logAll()
            ->logOnlyDirty();
    }
    //
}
