<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'category_id',
        'brand_id',
        'unit_id',
        'name',
        'code',
        'purchase_price',
        'retailer_price',
        'dealer_price',
        'wholesale_price',
        'stock',
        'alert_quantity',
        'tax_rate',
        'image_url',
        'description',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'retailer_price' => 'decimal:2',
        'dealer_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'stock' => 'decimal:2',
        'alert_quantity' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    /**
     * Helper to get price based on customer tier
     */
    public function getPriceForTier(string $tier = 'retailer'): float
    {
        return match ($tier) {
            'dealer' => (float) ($this->dealer_price > 0 ? $this->dealer_price : $this->retailer_price),
            'wholesaler' => (float) ($this->wholesale_price > 0 ? $this->wholesale_price : $this->retailer_price),
            default => (float) $this->retailer_price,
        };
    }
}
