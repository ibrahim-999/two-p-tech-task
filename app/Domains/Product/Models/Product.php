<?php

namespace App\Domains\Product\Models;

use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected static $factory = ProductFactory::class;

    protected $fillable = [
        'name',
        'description',
        'price',
        'stock_quantity',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function isInStock(int $quantity = 1): bool
    {
        return $this->stock_quantity >= $quantity;
    }

    public function reduceStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }
}
