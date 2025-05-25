<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Domains\Order\Services\OrderService;
use App\Http\Resources\Order\OrderResource;
use App\Http\Resources\Order\OrderCollection;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private OrderService $orderService
    ) {}

    public function index(Request $request)
    {
        try {
            $orders = $this->orderService->getUserOrders($request->user()->id);
            return new OrderCollection($orders);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve orders', 500);
        }
    }

    public function show(Request $request, $id)
    {
        try {
            $order = $this->orderService->find($id);

            if (!$order || $order->user_id !== $request->user()->id) {
                return $this->errorResponse('Order not found', 404);
            }

            return $this->successResponse(
                new OrderResource($order),
                'Order retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve order', 500);
        }
    }
}
