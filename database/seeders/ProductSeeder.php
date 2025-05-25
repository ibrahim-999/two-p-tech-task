<?php

namespace Database\Seeders;

use App\Domains\Product\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        Product::factory()
            ->count(5)
            ->create();
    }
}
