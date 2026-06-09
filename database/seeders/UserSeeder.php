<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'puput12@gmail.com'],
            [
                'name' => 'puput12',
                'password' => 'cahya13',
                'is_admin' => true,
            ]
        );
    }
}
