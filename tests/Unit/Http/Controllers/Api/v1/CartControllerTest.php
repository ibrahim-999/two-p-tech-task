<?php

namespace Tests\Unit\Http\Controllers\Api\v1;


use App\Domains\Cart\Models\Cart;
use App\Domains\Product\Models\Product;
use App\Domains\User\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartControllerTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private $user;

    private $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
            'stock_quantity' => 50,
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_user_can_get_empty_cart()
    {
        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/carts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_id',
                    'user_id',
                    'items_count',
                    'total_amount',
                    'is_empty',
                    'items',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Cart retrieved successfully',
                'data' => [
                    'user_id' => $this->user->id,
                    'items_count' => 0,
                    'total_amount' => 0,
                    'is_empty' => true,
                ],
            ]);
    }

    /** @test */
    public function test_user_can_add_product_to_cart()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'item_id',
                    'product_id',
                    'quantity',
                    'action_performed',
                    'cart_summary' => [
                        'total_items',
                        'total_amount',
                    ],
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Product added to cart successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'quantity' => 2,
                    'action_performed' => 'added',
                    'cart_summary' => [
                        'total_items' => 2,
                        'total_amount' => 199.98,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('carts', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);
    }

    /** @test */
    public function test_user_can_update_cart_item_quantity()
    {
        Sanctum::actingAs($this->user);

        // First add a product
        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->putJson("/api/v1/carts/{$this->product->id}", [
            'quantity' => 5,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cart item updated successfully',
                'data' => [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'action_performed' => 'updated',
                    'cart_summary' => [
                        'total_items' => 5,
                        'total_amount' => 499.95,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('cart_items', [
            'product_id' => $this->product->id,
            'quantity' => 5,
        ]);
    }

    /** @test */
    public function test_user_can_remove_product_from_cart()
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->deleteJson("/api/v1/carts/{$this->product->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Product removed from cart successfully',
                'data' => [
                    'items_count' => 0,
                    'total_amount' => 0,
                    'is_empty' => true,
                ],
            ]);

        $this->assertDatabaseMissing('cart_items', [
            'product_id' => $this->product->id,
        ]);
    }

    /** @test */
    public function test_user_can_clear_entire_cart()
    {
        Sanctum::actingAs($this->user);

        $product2 = Product::factory()->create([
            'price' => 49.99,
            'stock_quantity' => 30,
            'is_active' => true,
        ]);

        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $this->postJson('/api/v1/carts', [
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);

        $response = $this->deleteJson('/api/v1/cart/clear');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cart cleared successfully',
                'data' => [
                    'items_count' => 0,
                    'total_amount' => 0,
                    'is_empty' => true,
                ],
            ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(0, $cart->items()->count());
    }

    /** @test */
    public function test_user_can_get_cart_summary()
    {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response = $this->getJson('/api/v1/cart/summary');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'cart_id',
                    'items_count',
                    'total_amount',
                    'is_empty',
                ],
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Cart summary retrieved successfully',
                'data' => [
                    'items_count' => 3,
                    'total_amount' => 299.97,
                    'is_empty' => false,
                ],
            ]);
    }

    /** @test */
    public function test_show_method_returns_cart_summary()
    {
        Sanctum::actingAs($this->user);

        // Add a product
        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->getJson('/api/v1/carts/999');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Cart summary retrieved successfully',
                'data' => [
                    'items_count' => 2,
                    'total_amount' => 199.98,
                    'is_empty' => false,
                ],
            ]);
    }

    /** @test */
    public function test_validation_errors_for_add_to_cart()
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson('/api/v1/carts', [
            'quantity' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);

        $response = $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $response = $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 101,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $response = $this->postJson('/api/v1/carts', [
            'product_id' => 999999,
            'quantity' => 2,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['product_id']);
    }

    /** @test */
    public function test_validation_errors_for_update_cart_item()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson("/api/v1/carts/{$this->product->id}", []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);

        $response = $this->putJson("/api/v1/carts/{$this->product->id}", [
            'quantity' => 0,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['quantity']);
    }

    /** @test */
    public function test_unauthorized_access_returns_401()
    {
        $endpoints = [
            ['method' => 'get', 'uri' => '/api/v1/carts'],
            ['method' => 'post', 'uri' => '/api/v1/carts', 'data' => ['product_id' => 1, 'quantity' => 1]],
            ['method' => 'get', 'uri' => '/api/v1/carts/1'],
            ['method' => 'put', 'uri' => '/api/v1/carts/1', 'data' => ['quantity' => 2]],
            ['method' => 'delete', 'uri' => '/api/v1/carts/1'],
            ['method' => 'delete', 'uri' => '/api/v1/cart/clear'],
            ['method' => 'get', 'uri' => '/api/v1/cart/summary'],
        ];

        foreach ($endpoints as $endpoint) {
            $data = $endpoint['data'] ?? [];
            $response = $this->{$endpoint['method'].'Json'}($endpoint['uri'], $data);

            $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.',
                ]);
        }
    }

    /** @test */
    public function test_insufficient_stock_validation()
    {
        Sanctum::actingAs($this->user);

        $limitedProduct = Product::factory()->create([
            'price' => 50.00,
            'stock_quantity' => 2,
            'is_active' => true,
        ]);

        // Try to add more than available stock
        $response = $this->postJson('/api/v1/carts', [
            'product_id' => $limitedProduct->id,
            'quantity' => 5,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Insufficient stock available',
            ]);
    }

    /** @test */
    public function test_inactive_product_validation()
    {
        Sanctum::actingAs($this->user);

        $inactiveProduct = Product::factory()->create([
            'price' => 50.00,
            'stock_quantity' => 10,
            'is_active' => false,
        ]);

        $response = $this->postJson('/api/v1/carts', [
            'product_id' => $inactiveProduct->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(400)
            ->assertJson([
                'success' => false,
                'message' => 'Product is not available',
            ]);
    }

    /** @test */
    public function test_adding_same_product_multiple_times_updates_quantity()
    {
        Sanctum::actingAs($this->user);

        $response1 = $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response1->assertStatus(201);

        $response2 = $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 3,
        ]);

        $response2->assertStatus(201)
            ->assertJson([
                'data' => [
                    'quantity' => 3,
                    'cart_summary' => [
                        'total_items' => 3,
                        'total_amount' => 299.97,
                    ],
                ],
            ]);

        $cart = Cart::where('user_id', $this->user->id)->first();
        $this->assertEquals(1, $cart->items()->count());
        $this->assertEquals(3, $cart->items()->first()->quantity);
    }

    /** @test */
    public function test_cart_item_not_found_error()
    {
        Sanctum::actingAs($this->user);

        $response = $this->putJson('/api/v1/carts/999', [
            'quantity' => 2,
        ]);

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
            ]);

        $response = $this->deleteJson('/api/v1/carts/999');

        $response->assertStatus(400)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    /** @test */
    public function test_complete_cart_workflow()
    {
        Sanctum::actingAs($this->user);

        $product2 = Product::factory()->create([
            'name' => 'Second Product',
            'price' => 25.50,
            'stock_quantity' => 20,
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/v1/carts');
        $response->assertJson(['data' => ['is_empty' => true]]);

        $this->postJson('/api/v1/carts', [
            'product_id' => $this->product->id,
            'quantity' => 2,
        ])->assertStatus(201);

        $this->postJson('/api/v1/carts', [
            'product_id' => $product2->id,
            'quantity' => 3,
        ])->assertStatus(201);

        $response = $this->getJson('/api/v1/carts');
        $response->assertJson([
            'data' => [
                'items_count' => 5,
                'total_amount' => 276.48,
                'is_empty' => false,
            ],
        ]);

        $this->putJson("/api/v1/carts/{$this->product->id}", [
            'quantity' => 1,
        ])->assertStatus(200);

        $response = $this->getJson('/api/v1/cart/summary');
        $response->assertJson([
            'data' => [
                'items_count' => 4,
                'total_amount' => 176.49,
            ],
        ]);

        // 7. Remove second product
        $this->deleteJson("/api/v1/carts/{$product2->id}")
            ->assertStatus(200);

        $response = $this->getJson('/api/v1/carts');
        $response->assertJson([
            'data' => [
                'items_count' => 1,
                'total_amount' => 99.99,
            ],
        ]);

        $this->deleteJson('/api/v1/cart/clear')
            ->assertStatus(200);

        $response = $this->getJson('/api/v1/carts');
        $response->assertJson([
            'data' => [
                'items_count' => 0,
                'total_amount' => 0,
                'is_empty' => true,
            ],
        ]);
    }
}

