<?php

namespace App\Models\fixedasset;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $table = 'fa_asset_types';

    protected $fillable = [
        'name',
    ];
}
