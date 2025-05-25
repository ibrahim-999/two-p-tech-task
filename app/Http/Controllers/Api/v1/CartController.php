<?php

namespace App\Http\Controllers\Api\v1;

use App\Application\Cart\AddToCartUseCase;
use App\Application\Cart\ClearCartUseCase;
use App\Application\Cart\GetCartUseCase;
use App\Application\Cart\RemoveFromCartUseCase;
use App\Application\Cart\UpdateCartItemUseCase;
use App\Domains\Cart\Services\CartService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use App\Http\Resources\Cart\CartItemActionResource;
use App\Http\Resources\Cart\CartResource;
use App\Http\Resources\Cart\CartSummaryResource;
use App\Traits\ApiResponseTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use OpenApi\Attributes as OA;

#[OA\Tag(name: 'Cart', description: 'Shopping cart management')]
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

    /**
     * Get the authenticated user's ID safely
     */
    private function getAuthenticatedUserId(): int
    {
        $userId = Auth::id();

        if (! $userId) {
            throw new \Exception('User not authenticated', 401);
        }

        return $userId;
    }

    #[OA\Get(
        path: '/api/v1/carts',
        summary: "Get user's cart",
        description: "Retrieve the authenticated user's shopping cart with all items",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Cart retrieved successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'cart_id' => new OA\Property(property: 'cart_id', type: 'integer', example: 1),
                                'user_id' => new OA\Property(property: 'user_id', type: 'integer', example: 1),
                                'items_count' => new OA\Property(property: 'items_count', type: 'integer', example: 3),
                                'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
                                'is_empty' => new OA\Property(property: 'is_empty', type: 'boolean', example: false),
                                'items' => new OA\Property(
                                    property: 'items',
                                    type: 'array',
                                    items: new OA\Items(
                                        properties: [
                                            'id' => new OA\Property(property: 'id', type: 'integer', example: 1),
                                            'product_id' => new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                            'product_name' => new OA\Property(property: 'product_name', type: 'string', example: 'iPhone 15'),
                                            'product_description' => new OA\Property(property: 'product_description', type: 'string', example: 'Latest iPhone model'),
                                            'unit_price' => new OA\Property(property: 'unit_price', type: 'number', format: 'float', example: 999.99),
                                            'quantity' => new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                            'total_price' => new OA\Property(property: 'total_price', type: 'number', format: 'float', example: 1999.98),
                                            'stock_available' => new OA\Property(property: 'stock_available', type: 'integer', example: 50),
                                        ],
                                        type: 'object'
                                    )
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function index(Request $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $cartData = $this->getCartUseCase->execute($userId);

            $cart = $this->cartService->getCartWithDetails($userId);

            return $this->successResponse(
                new CartResource($cart),
                'Cart retrieved successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 500;
            $message = $e->getCode() === 401 ? $e->getMessage() : 'Failed to retrieve cart';

            return $this->errorResponse($message, $statusCode);
        }
    }

    #[OA\Post(
        path: '/api/v1/carts',
        summary: 'Add product to cart',
        description: "Add a product to the authenticated user's shopping cart",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['product_id', 'quantity'],
                properties: [
                    'product_id' => new OA\Property(property: 'product_id', type: 'integer', example: 1, description: 'Product ID'),
                    'quantity' => new OA\Property(property: 'quantity', type: 'integer', example: 2, minimum: 1, maximum: 100, description: 'Quantity to add'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Product added to cart successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Product added to cart successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'item_id' => new OA\Property(property: 'item_id', type: 'integer', example: 1),
                                'product_id' => new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                'quantity' => new OA\Property(property: 'quantity', type: 'integer', example: 2),
                                'action_performed' => new OA\Property(property: 'action_performed', type: 'string', example: 'added'),
                                'cart_summary' => new OA\Property(
                                    property: 'cart_summary',
                                    properties: [
                                        'total_items' => new OA\Property(property: 'total_items', type: 'integer', example: 3),
                                        'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request - validation error'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function store(AddToCartRequest $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $cartItem = $this->addToCartUseCase->execute(
                $userId,
                $request->product_id,
                $request->quantity
            );

            $cartSummary = $this->cartService->getCartSummary($userId);

            $cartItem->additional = [
                'action' => 'added',
                'cart_items_count' => $cartSummary['items_count'],
                'cart_total' => $cartSummary['total_amount'],
            ];

            return $this->successResponse(
                new CartItemActionResource($cartItem),
                'Product added to cart successfully',
                201
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 400;

            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    #[OA\Get(
        path: '/api/v1/carts/{id}',
        summary: 'Get cart summary',
        description: "Get a summary of the user's cart (items count, total amount)",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: "Cart ID (ignored, uses authenticated user's cart)"),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Cart summary retrieved successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'cart_id' => new OA\Property(property: 'cart_id', type: 'integer', example: 1),
                                'items_count' => new OA\Property(property: 'items_count', type: 'integer', example: 3),
                                'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
                                'is_empty' => new OA\Property(property: 'is_empty', type: 'boolean', example: false),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function show(Request $request, $id)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $cartSummary = $this->cartService->getCartSummary($userId);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Cart summary retrieved successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 500;
            $message = $e->getCode() === 401 ? $e->getMessage() : 'Failed to get cart summary';

            return $this->errorResponse($message, $statusCode);
        }
    }

    #[OA\Put(
        path: '/api/v1/carts/{productId}',
        summary: 'Update cart item quantity',
        description: 'Update the quantity of a specific product in the cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Product ID'),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['quantity'],
                properties: [
                    'quantity' => new OA\Property(property: 'quantity', type: 'integer', example: 3, minimum: 1, maximum: 100, description: 'New quantity'),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart item updated successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Cart item updated successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'item_id' => new OA\Property(property: 'item_id', type: 'integer', example: 1),
                                'product_id' => new OA\Property(property: 'product_id', type: 'integer', example: 1),
                                'quantity' => new OA\Property(property: 'quantity', type: 'integer', example: 3),
                                'action_performed' => new OA\Property(property: 'action_performed', type: 'string', example: 'updated'),
                                'cart_summary' => new OA\Property(
                                    property: 'cart_summary',
                                    properties: [
                                        'total_items' => new OA\Property(property: 'total_items', type: 'integer', example: 4),
                                        'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 399.99),
                                    ],
                                    type: 'object'
                                ),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request - validation error'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function update(UpdateCartItemRequest $request, $productId)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $cartItem = $this->updateCartItemUseCase->execute(
                $userId,
                $productId,
                $request->quantity
            );

            $cartSummary = $this->cartService->getCartSummary($userId);

            $cartItem->additional = [
                'action' => 'updated',
                'cart_items_count' => $cartSummary['items_count'],
                'cart_total' => $cartSummary['total_amount'],
            ];

            return $this->successResponse(
                new CartItemActionResource($cartItem),
                'Cart item updated successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 400;

            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    #[OA\Delete(
        path: '/api/v1/carts/{productId}',
        summary: 'Remove product from cart',
        description: 'Remove a specific product from the shopping cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        parameters: [
            new OA\Parameter(name: 'productId', in: 'path', required: true, schema: new OA\Schema(type: 'integer'), description: 'Product ID to remove'),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Product removed from cart successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Product removed from cart successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'cart_id' => new OA\Property(property: 'cart_id', type: 'integer', example: 1),
                                'items_count' => new OA\Property(property: 'items_count', type: 'integer', example: 2),
                                'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 199.99),
                                'is_empty' => new OA\Property(property: 'is_empty', type: 'boolean', example: false),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 400, description: 'Bad request - item not found'),
            new OA\Response(response: 401, description: 'Unauthorized'),
        ]
    )]
    public function destroy(Request $request, $productId)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $this->removeFromCartUseCase->execute($userId, $productId);

            $cartSummary = $this->cartService->getCartSummary($userId);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Product removed from cart successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 400;

            return $this->errorResponse($e->getMessage(), $statusCode);
        }
    }

    #[OA\Delete(
        path: '/api/v1/cart/clear',
        summary: 'Clear entire cart',
        description: 'Remove all items from the shopping cart',
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart cleared successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Cart cleared successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'cart_id' => new OA\Property(property: 'cart_id', type: 'integer', example: 1),
                                'items_count' => new OA\Property(property: 'items_count', type: 'integer', example: 0),
                                'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 0.00),
                                'is_empty' => new OA\Property(property: 'is_empty', type: 'boolean', example: true),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function clear(Request $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $this->clearCartUseCase->execute($userId);

            $cartSummary = $this->cartService->getCartSummary($userId);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Cart cleared successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 500;
            $message = $e->getCode() === 401 ? $e->getMessage() : 'Failed to clear cart';

            return $this->errorResponse($message, $statusCode);
        }
    }

    #[OA\Get(
        path: '/api/v1/cart/summary',
        summary: 'Get detailed cart summary',
        description: "Get a detailed summary of the user's shopping cart",
        security: [['bearerAuth' => []]],
        tags: ['Cart'],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Cart summary retrieved successfully',
                content: new OA\JsonContent(
                    properties: [
                        'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                        'message' => new OA\Property(property: 'message', type: 'string', example: 'Cart summary retrieved successfully'),
                        'data' => new OA\Property(
                            property: 'data',
                            properties: [
                                'cart_id' => new OA\Property(property: 'cart_id', type: 'integer', example: 1),
                                'items_count' => new OA\Property(property: 'items_count', type: 'integer', example: 3),
                                'total_amount' => new OA\Property(property: 'total_amount', type: 'number', format: 'float', example: 299.99),
                                'is_empty' => new OA\Property(property: 'is_empty', type: 'boolean', example: false),
                            ],
                            type: 'object'
                        ),
                    ]
                )
            ),
            new OA\Response(response: 401, description: 'Unauthorized'),
            new OA\Response(response: 500, description: 'Server error'),
        ]
    )]
    public function summary(Request $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $cartSummary = $this->cartService->getCartSummary($userId);

            return $this->successResponse(
                new CartSummaryResource($cartSummary),
                'Cart summary retrieved successfully'
            );
        } catch (\Exception $e) {
            $statusCode = $e->getCode() === 401 ? 401 : 500;
            $message = $e->getCode() === 401 ? $e->getMessage() : 'Failed to get cart summary';

            return $this->errorResponse($message, $statusCode);
        }
    }
}
