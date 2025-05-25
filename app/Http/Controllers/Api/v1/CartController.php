<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Application\Cart\AddToCartUseCase;
use App\Application\Cart\UpdateCartItemUseCase;
use App\Application\Cart\RemoveFromCartUseCase;
use App\Application\Cart\GetCartUseCase;
use App\Application\Cart\ClearCartUseCase;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Cart\CartItemActionResource;
use App\Http\Resources\Cart\CartSummaryResource;
use App\Domains\Cart\Services\CartService;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AddToCartUseCase $addToCartUseCase,
        private UpdateCartItemUseCase $updateCartItemUseCase,
        private RemoveFromCartUseCase $removeFromCartUseCase,
        private GetCartUseCase $getCartUseCase,
        private ClearCartUseCase $clearCartUseCase,
        private CartService $cartService
    ) {}

    public function index(Request $request)
    {
        try {
            $cart = $this->getCartUseCase->execute($request->user()->id);
            return $this->successResponse(
                new CartResource($cart),
                'Cart retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve cart', 500);
        }
    }

    public function store(AddToCartRequest $request)
    {
        try {
            $cartItem = $this->addToCartUseCase->execute(
                $request->user()->id,
                $request->product_id,
                $request->quantity
            );

            $cartSummary = $this->cartService->getCartSummary($request->user()->id);

            $cartItem->additional = [
                'action' => 'added',
                'cart_items_count' => $cartSummary['items_count'],
                'cart_total' => $cartSummary['total_amount']
            ];

            return $this->successResponse(
                new CartItemActionResource($cartItem),
                'Product added to cart successfully',
                201
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function update(UpdateCartItemRequest $request, $productId)
    {
        try {
            $cartItem = $this->updateCartItemUseCase->execute(
                $request->user()->id,
                $productId,
                $request->quantity
            );

            $cartSummary = $this->cartService->getCartSummary($request->user()->id);

            $cartItem->additional = [
                'action' => 'updated',
                'cart_items_count' => $cartSummary['items_count'],
                'cart_total' => $cartSummary['total_amount']
            ];

            return $this->successResponse(
                new CartItemActionResource($cartItem),
                'Cart item updated successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function destroy(Request $request, $productId)
    {
        try {
            $this->removeFromCartUseCase->execute($request->user()->id, $productId);

            $cartSummary = $this->cartService->getCartSummary($request->user()->id);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Product removed from cart successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    public function clear(Request $request)
    {
        try {
            $this->clearCartUseCase->execute($request->user()->id);

            $cartSummary = $this->cartService->getCartSummary($request->user()->id);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Cart cleared successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to clear cart', 500);
        }
    }

    public function summary(Request $request)
    {
        try {
            $cartSummary = $this->cartService->getCartSummary($request->user()->id);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Cart summary retrieved successfully'
            );
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to get cart summary', 500);
        }
    }
}
