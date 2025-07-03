<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => bcrypt('admin123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
            [
                'name' => 'Usuario',
                'email' => 'user@gmail.com',
                'password' => bcrypt('user123'),
                'id_rol' => 2,
                'estado' => 1,
            ],
            [
                'name' => 'Mike',
                'email' => 'mg26667418@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
            [
                'name' => 'Mauri',
                'email' => 'maumenachotri224@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
            [
                'name' => 'Kiara',
                'email' => 'kiaritapino11@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
            [
                'name' => 'Evelyn',
                'email' => 'evelynburgoa04@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
            [
                'name' => 'Wawita',
                'email' => 'huanapacowara@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
            ],
        ];

        foreach ($usuarios as $usuarioData) {
            User::create($usuarioData);
        }
    }
}