<?php

namespace Tests\Unit\Http\Controllers\Api\v1;

use App\Domains\User\Models\User;
use Tests\TestCase;
use App\Http\Controllers\Api\v1\CartController;
use App\Application\Cart\AddToCartUseCase;
use App\Application\Cart\UpdateCartItemUseCase;
use App\Application\Cart\RemoveFromCartUseCase;
use App\Application\Cart\GetCartUseCase;
use App\Application\Cart\ClearCartUseCase;
use App\Domains\Cart\Services\CartService;
use App\Http\Requests\Cart\AddToCartRequest;
use App\Http\Requests\Cart\UpdateCartItemRequest;
use Illuminate\Http\Request;
use Mockery;
use Exception;

class CartControllerTest extends TestCase
{
    private $addToCartUseCase;
    private $updateCartItemUseCase;
    private $removeFromCartUseCase;
    private $getCartUseCase;
    private $clearCartUseCase;
    private $cartService;
    private $controller;
    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->addToCartUseCase = Mockery::mock(AddToCartUseCase::class);
        $this->updateCartItemUseCase = Mockery::mock(UpdateCartItemUseCase::class);
        $this->removeFromCartUseCase = Mockery::mock(RemoveFromCartUseCase::class);
        $this->getCartUseCase = Mockery::mock(GetCartUseCase::class);
        $this->clearCartUseCase = Mockery::mock(ClearCartUseCase::class);
        $this->cartService = Mockery::mock(CartService::class);

        $this->controller = new CartController(
            $this->addToCartUseCase,
            $this->updateCartItemUseCase,
            $this->removeFromCartUseCase,
            $this->getCartUseCase,
            $this->clearCartUseCase,
            $this->cartService
        );

        $this->user = User::factory()->create();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }


    public function index_returns_cart_successfully()
    {

        $request = Request::create('/api/v1/carts', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $mockCart = (object) [
            'id' => 1,
            'user_id' => $this->user->id,
            'items' => []
        ];

        $this->getCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCart);


        $response = $this->controller->index($request);


        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Cart retrieved successfully', $responseData['message']);
    }


    public function index_returns_error_when_exception_occurs()
    {

        $request = Request::create('/api/v1/carts', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $this->getCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id)
            ->andThrow(new Exception('Database error'));


        $response = $this->controller->index($request);


        $this->assertEquals(500, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Failed to retrieve cart', $responseData['message']);
    }


    public function store_adds_product_to_cart_successfully()
    {

        $request = Mockery::mock(AddToCartRequest::class);
        $request->shouldReceive('user')->andReturn($this->user);
        $request->shouldReceive('get')->with('product_id')->andReturn(1);
        $request->shouldReceive('get')->with('quantity')->andReturn(2);
        $request->product_id = 1;
        $request->quantity = 2;

        $mockCartItem = (object) [
            'id' => 1,
            'product_id' => 1,
            'quantity' => 2,
            'additional' => []
        ];

        $mockCartSummary = [
            'items_count' => 1,
            'total_amount' => 100.00
        ];

        $this->addToCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, 1, 2)
            ->andReturn($mockCartItem);

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCartSummary);


        $response = $this->controller->store($request);


        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Product added to cart successfully', $responseData['message']);
    }


    public function store_returns_error_when_exception_occurs()
    {

        $request = Mockery::mock(AddToCartRequest::class);
        $request->shouldReceive('user')->andReturn($this->user);
        $request->shouldReceive('get')->with('product_id')->andReturn(1);
        $request->shouldReceive('get')->with('quantity')->andReturn(2);
        $request->product_id = 1;
        $request->quantity = 2;

        $this->addToCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, 1, 2)
            ->andThrow(new Exception('Product not found'));


        $response = $this->controller->store($request);


        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Product not found', $responseData['message']);
    }


    public function update_modifies_cart_item_successfully()
    {

        $request = Mockery::mock(UpdateCartItemRequest::class);
        $request->shouldReceive('user')->andReturn($this->user);
        $request->shouldReceive('get')->with('quantity')->andReturn(3);
        $request->quantity = 3;

        $productId = 1;

        $mockCartItem = (object) [
            'id' => 1,
            'product_id' => $productId,
            'quantity' => 3,
            'additional' => []
        ];

        $mockCartSummary = [
            'items_count' => 1,
            'total_amount' => 150.00
        ];

        $this->updateCartItemUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, $productId, 3)
            ->andReturn($mockCartItem);

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCartSummary);


        $response = $this->controller->update($request, $productId);


        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Cart item updated successfully', $responseData['message']);
    }


    public function update_returns_error_when_exception_occurs()
    {

        $request = Mockery::mock(UpdateCartItemRequest::class);
        $request->shouldReceive('user')->andReturn($this->user);
        $request->shouldReceive('get')->with('quantity')->andReturn(3);
        $request->quantity = 3;

        $productId = 1;

        $this->updateCartItemUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, $productId, 3)
            ->andThrow(new Exception('Cart item not found'));


        $response = $this->controller->update($request, $productId);


        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Cart item not found', $responseData['message']);
    }


    public function destroy_removes_product_from_cart_successfully()
    {

        $request = Request::create('/api/v1/carts/1', 'DELETE');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $productId = 1;

        $mockCartSummary = [
            'items_count' => 0,
            'total_amount' => 0.00
        ];

        $this->removeFromCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, $productId)
            ->andReturn(true);

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCartSummary);

        $response = $this->controller->destroy($request, $productId);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Product removed from cart successfully', $responseData['message']);
    }

    public function destroy_returns_error_when_exception_occurs()
    {
        $request = Request::create('/api/v1/carts/1', 'DELETE');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $productId = 1;

        $this->removeFromCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id, $productId)
            ->andThrow(new Exception('Cart item not found'));

        $response = $this->controller->destroy($request, $productId);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Cart item not found', $responseData['message']);
    }

    public function clear_empties_cart_successfully()
    {
        $request = Request::create('/api/v1/carts', 'DELETE');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $mockCartSummary = [
            'items_count' => 0,
            'total_amount' => 0.00
        ];

        $this->clearCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id)
            ->andReturn(true);

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCartSummary);

        $response = $this->controller->clear($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Cart cleared successfully', $responseData['message']);
    }

    public function clear_returns_error_when_exception_occurs()
    {
        $request = Request::create('/api/v1/carts', 'DELETE');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $this->clearCartUseCase
            ->shouldReceive('execute')
            ->once()
            ->with($this->user->id)
            ->andThrow(new Exception('Database error'));

        $response = $this->controller->clear($request);

        $this->assertEquals(500, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Failed to clear cart', $responseData['message']);
    }

    public function summary_returns_cart_summary_successfully()
    {
        $request = Request::create('/api/v1/carts/summary', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $mockCartSummary = [
            'items_count' => 2,
            'total_amount' => 250.00
        ];

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andReturn($mockCartSummary);

        $response = $this->controller->summary($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Cart summary retrieved successfully', $responseData['message']);
    }

    public function summary_returns_error_when_exception_occurs()
    {
        $request = Request::create('/api/v1/carts/summary', 'GET');
        $request->setUserResolver(function () {
            return $this->user;
        });

        $this->cartService
            ->shouldReceive('getCartSummary')
            ->once()
            ->with($this->user->id)
            ->andThrow(new Exception('Database error'));

        $response = $this->controller->summary($request);

        $this->assertEquals(500, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Failed to get cart summary', $responseData['message']);
    }
}
