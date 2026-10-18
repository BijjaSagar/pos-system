<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'plan_id',
        'plan_expires_at',
        'phone',
        'email',
        'address',
        'currency_symbol',
        'currency_code',
        'tax_number',
        'logo_url',
        'primary_color',
        'currency_position',
        'thermal_paper_size',
        'receipt_header',
        'receipt_footer',
        'status',
    ];

    public function formatPrice($amount): string
    {
        $formatted = number_format((float)$amount, 2);
        $symbol = $this->currency_symbol ?? '₹';
        if (($this->currency_position ?? 'prefix') === 'suffix') {
            return "{$formatted} {$symbol}";
        }
        return "{$symbol}{$formatted}";
    }

    protected $casts = [
        'plan_expires_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }
}
