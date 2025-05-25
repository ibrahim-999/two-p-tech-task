<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Application\Cart\AddToCartUseCase;
use App\Application\Cart\UpdateCartItemUseCase;
use App\Application\Cart\RemoveFromCartUseCase;
use App\Application\Cart\GetCartUseCase;
use App\Application\Cart\ClearCartUseCase;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use App\Http\Requests\UpdateCartItemRequest;

class CartController extends Controller
{
    use ApiResponseTrait;

    public function __construct(
        private AddToCartUseCase $addToCartUseCase,
        private UpdateCartItemUseCase $updateCartItemUseCase,
        private RemoveFromCartUseCase $removeFromCartUseCase,
        private GetCartUseCase $getCartUseCase,
        private ClearCartUseCase $clearCartUseCase
    ) {}

    /**
     * View Cart Contents
     * GET /api/v1/cart
     */
    public function index(Request $request)
    {
        try {
            $cart = $this->getCartUseCase->execute($request->user()->id);
            return $this->successResponse($cart, 'Cart retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve cart', 500);
        }
    }

    /**
     * Add Product to Cart
     * POST /api/v1/cart
     */
    public function store(AddToCartRequest $request)
    {
        try {
            $cartItem = $this->addToCartUseCase->execute(
                $request->user()->id,
                $request->product_id,
                $request->quantity
            );

            return $this->successResponse($cartItem, 'Product added to cart successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Update Cart Quantity
     * PUT /api/v1/cart/{productId}
     */
    public function update(UpdateCartItemRequest $request, $productId)
    {
        try {
            $cartItem = $this->updateCartItemUseCase->execute(
                $request->user()->id,
                $productId,
                $request->quantity
            );

            return $this->successResponse($cartItem, 'Cart item updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Remove Product from Cart
     * DELETE /api/v1/cart/{productId}
     */
    public function destroy(Request $request, $productId)
    {
        try {
            $this->removeFromCartUseCase->execute($request->user()->id, $productId);
            return $this->successResponse(null, 'Product removed from cart successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * Clear entire cart
     * DELETE /api/v1/cart
     */
    public function clear(Request $request)
    {
        try {
            $this->clearCartUseCase->execute($request->user()->id);
            return $this->successResponse(null, 'Cart cleared successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to clear cart', 500);
        }
    }
}
