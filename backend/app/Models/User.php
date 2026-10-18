<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'shop_id',
        'role',
        'phone',
        'is_active',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    public function shop()
    {
        return $this->belongsTo(Shop::class);
    }

    public function getShopAttribute()
    {
        if ($this->relationLoaded('shop')) {
            $related = $this->getRelation('shop');
            if ($related) {
                return $related;
            }
        }

        $shopId = $this->attributes['shop_id'] ?? null;
        if ($shopId) {
            $shop = Shop::find($shopId);
            if ($shop) {
                return $shop;
            }
        }

        return Shop::first();
    }

    public function getShopIdAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }

        return Shop::first()?->id ?? 1;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isStoreOwner(): bool
    {
        return $this->role === 'store_owner';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function isEmployee(): bool
    {
        return $this->role === 'employee';
    }

    public function hasPermission(string $permission): bool
    {
        if ($this->isSuperAdmin() || $this->isStoreOwner()) {
            return true;
        }

        $perms = $this->permissions ?? [];
        return in_array($permission, $perms, true);
    }

    public static function defaultPermissionsForRole(string $role): array
    {
        return match ($role) {
            'store_owner' => [
                'pos_access',
                'products_manage',
                'purchases_manage',
                'reports_view',
                'users_manage',
                'settings_manage',
            ],
            'manager' => [
                'pos_access',
                'products_manage',
                'purchases_manage',
                'reports_view',
                'users_manage',
            ],
            'cashier' => [
                'pos_access',
                'products_view',
                'customers_manage',
            ],
            'employee' => [
                'products_view',
                'inventory_manage',
            ],
            default => ['pos_access'],
        };
    }
}
