<?php

namespace App\Domains\User\Models;

use App\Domains\Cart\Models\Cart;
use App\Domains\Order\Models\Order;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected static $factory = UserFactory::class;
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function cart(): HasOne
    {
        return $this->hasOne(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paidOrders(): HasMany
    {
        return $this->hasMany(Order::class)->where('status', 'paid');
    }

    public function pendingOrders(): HasMany
    {
        return $this->hasMany(Order::class)->where('status', 'pending');
    }

    public function getTotalSpentAttribute(): float
    {
        return $this->paidOrders()->sum('total_amount');
    }

    public function getCurrentCartItemsCountAttribute(): int
    {
        return $this->cart ? $this->cart->getItemsCount() : 0;
    }

    public function getCurrentCartTotalAttribute(): float
    {
        return $this->cart ? $this->cart->getTotalAmount() : 0.0;
    }

    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    public function getMemberSinceAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeWithOrders($query)
    {
        return $query->has('orders');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->has('orders')
                ->orWhere('updated_at', '>=', now()->subDays(30));
        });
    }
}
