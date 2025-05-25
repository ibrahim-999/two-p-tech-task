<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),

            'profile_stats' => [
                'total_orders' => $this->orders()->count(),
                'total_spent' => $this->orders()->where('status', 'paid')->sum('total_amount'),
                'pending_orders' => $this->orders()->where('status', 'pending')->count(),
                'cart_items' => $this->cart ? $this->cart->getItemsCount() : 0,
                'member_since' => $this->created_at->diffForHumans(),
            ],

            'current_cart' => $this->when($this->cart, function () {
                return [
                    'id' => $this->cart->id,
                    'items_count' => $this->cart->getItemsCount(),
                    'total_amount' => $this->cart->getTotalAmount(),
                    'last_updated' => $this->cart->updated_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }
}
