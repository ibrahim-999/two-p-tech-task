<?php

namespace App\Domains\Order\Models;

use App\Domains\User\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';

    const STATUS_PAID = 'paid';

    const STATUS_CANCELLED = 'cancelled';

    const STATUS_DELIVERED = 'delivered';

    protected $fillable = [
        'user_id',
        'order_number',
        'total_amount',
        'status',
        'payment_gateway',
        'payment_reference',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function generateOrderNumber(): string
    {
        return 'ORD-'.date('Y').'-'.str_pad($this->id, 6, '0', STR_PAD_LEFT);
    }
}
