<?php

namespace App\Providers;

use App\Domains\Cart\Repositories\CartRepositoryInterface;
use App\Domains\Order\Repositories\OrderRepositoryInterface;
use App\Domains\Product\Repositories\ProductRepositoryInterface;
use App\Domains\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Repositories\CartRepository;
use App\Infrastructure\Repositories\OrderRepository;
use App\Infrastructure\Repositories\ProductRepository;
use App\Infrastructure\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, CartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    public function boot()
    {
    }

}
