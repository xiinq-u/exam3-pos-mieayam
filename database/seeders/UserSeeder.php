<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Membuat akun admin awal untuk login ke sistem.
     * Jika email sudah ada, data akun akan diperbarui.
     */
    public function run(): void
    {
        foreach ([
            ['name' => 'Puput Owner', 'email' => 'puput12@gmail.com', 'password' => 'cahya13', 'role' => 'owner', 'is_admin' => true],
            ['name' => 'Kasir Demo', 'email' => 'kasir@gmail.test', 'password' => 'cahya14', 'role' => 'cashier', 'is_admin' => false],
            ['name' => 'Dapur Demo', 'email' => 'dapur@gmail.test', 'password' => 'cahya15', 'role' => 'kitchen', 'is_admin' => false],
        ] as $user) {
            User::updateOrCreate(['email' => $user['email']], $user);
        }
    }
}
