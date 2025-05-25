<?php

namespace App\Domains\Cart\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = ['user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function getTotalAmount(): float
    {
        return $this->items->sum(function ($item) {
            return $item->quantity * $item->product->price;
        });
    }

    public function getItemsCount(): int
    {
        return $this->items->sum('quantity');
    }

    public function clear(): void
    {
        $this->items()->delete();
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    public function hasItem($productId): bool
    {
        return $this->items()->where('product_id', $productId)->exists();
    }

    public function getItem($productId)
    {
        return $this->items()->where('product_id', $productId)->first();
    }
}
