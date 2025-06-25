<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('admin123'),
            'id_rol' => 1
        ]);

        User::create([
            'name' => 'Usuario',
            'email' => 'user@gmail.com',
            'password' => bcrypt('user123'),
            'id_rol' => 2
        ]);
    }
}
