<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PermissionType extends Model
{
    protected $table = 'permission_types';

    protected $fillable = [
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function permissions()
    {
        return $this->hasMany(Permission::class, 'permission_type_id');
    }
}
