<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'regular_price',
        'offer_price',
        'duration_days',
        'product_limit',
        'invoice_limit',
        'user_limit',
        'warehouse_limit',
        'hrm_enabled',
        'whatsapp_marketing_enabled',
        'is_active',
        'description',
    ];

    protected $casts = [
        'regular_price' => 'decimal:2',
        'offer_price' => 'decimal:2',
        'hrm_enabled' => 'boolean',
        'whatsapp_marketing_enabled' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function shops()
    {
        return $this->hasMany(Shop::class);
    }
}
