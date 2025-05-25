<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Application\Order\GetOrderUseCase;
use App\Application\Order\GetUserOrdersUseCase;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private GetOrderUseCase $getOrderUseCase,
        private GetUserOrdersUseCase $getUserOrdersUseCase
    ) {}

    /**
     * Get user's orders
     * GET /api/v1/orders
     */
    public function index(Request $request)
    {
        try {
            $orders = $this->getUserOrdersUseCase->execute($request->user()->id);

            return $this->successResponse([
                'orders' => $orders->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'status' => $order->status,
                        'total_amount' => $order->total_amount,
                        'created_at' => $order->created_at,
                        'items_count' => $order->items->count(),
                        'items' => $order->items->map(function ($item) {
                            return [
                                'product_name' => $item->product_name,
                                'quantity' => $item->quantity,
                                'price' => $item->price
                            ];
                        })
                    ];
                })
            ], 'Orders retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders', 500);
        }
    }

    /**
     * Get specific order
     * GET /api/v1/orders/{id}
     */
    public function show(Request $request, $id)
    {
        try {
            $order = $this->getOrderUseCase->execute($id);

            if (!$order || $order->user_id !== $request->user()->id) {
                return $this->errorResponse('Order not found', 404);
            }

            return $this->successResponse([
                'order' => [
                    'id' => $order->id,
                    'order_number' => $order->order_number,
                    'status' => $order->status,
                    'total_amount' => $order->total_amount,
                    'payment_gateway' => $order->payment_gateway,
                    'created_at' => $order->created_at,
                    'items' => $order->items->map(function ($item) {
                        return [
                            'id' => $item->id,
                            'product_name' => $item->product_name,
                            'quantity' => $item->quantity,
                            'price' => $item->price,
                            'total' => $item->quantity * $item->price
                        ];
                    })
                ]
            ], 'Order retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve order', 500);
        }
    }
}
