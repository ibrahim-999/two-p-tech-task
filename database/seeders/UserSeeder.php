<?php

namespace Database\Seeders;

use App\Domains\User\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()
            ->count(5)
            ->create();
    }
}
