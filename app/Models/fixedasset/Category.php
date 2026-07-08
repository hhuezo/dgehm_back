<?php

namespace App\Models\fixedasset;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;

class Category extends Model
{
    protected $table = 'fa_categories';

    protected $primaryKey = 'id';

    protected $keyType = 'int';

    public $incrementing = true;

    protected $fillable = [
        'name',
        'code',
        'useful_life',
        'fa_specific_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'useful_life' => 'integer',
    ];

    public function specific()
    {
        return $this->belongsTo(Specific::class, 'fa_specific_id');
    }

    public function responsibles()
    {
        return $this->belongsToMany(
            Employee::class,
            'fa_category_employee',
            'fa_category_id',
            'adm_employee_id'
        )->withTimestamps();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('categories')
            ->logAll()
            ->logOnlyDirty();
    }
}
