<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use BelongsToTenant;

    protected $fillable = ['shop_id', 'name'];
}
