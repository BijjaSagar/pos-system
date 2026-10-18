<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Party extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'shop_id',
        'type', // customer, supplier
        'customer_tier', // retailer, dealer, wholesaler
        'name',
        'phone',
        'email',
        'address',
        'opening_balance',
        'current_balance',
        'credit_limit',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'current_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
    ];

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function ledgers()
    {
        return $this->hasMany(Ledger::class);
    }
}
