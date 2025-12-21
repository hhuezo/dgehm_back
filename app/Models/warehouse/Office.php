<?php

namespace App\Models\warehouse;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Office extends Model
{
    use HasFactory;

    protected $table = 'wh_offices';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'phone',
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


    public function users()
    {
        return $this->belongsToMany(
            User::class,
            'user_wh_office',
            'wh_office_id',
            'user_id'
        )->withTimestamps();
    }
}
