<?php

namespace App\Models\Traits;

use App\Models\Shop;
use Illuminate\Database\Eloquent\Builder;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (empty($model->shop_id) && auth()->check() && auth()->user()->shop_id) {
                $model->shop_id = auth()->user()->shop_id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->shop_id && auth()->user()->role !== 'super_admin') {
                $builder->where($builder->getModel()->getTable() . '.shop_id', auth()->user()->shop_id);
            }
        });
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }
}
