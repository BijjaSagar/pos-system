<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'name',
        'phone',
        'email',
        'address',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
