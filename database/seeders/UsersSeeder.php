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
            'id_rol' => 1,
            'dos_pasos_habilitado' => 0
        ]);

        User::create([
            'name' => 'Usuario',
            'email' => 'user@gmail.com',
            'password' => bcrypt('user123'),
            'id_rol' => 2,
            'dos_pasos_habilitado' => 0
        ]);
        User::create([
            'name' => 'Mike',
            'email' => 'mg26667418@gmail.com',
            'password' => bcrypt('contra123'),
            'id_rol' => 1,
            'dos_pasos_habilitado' => 1
        ]);
        User::create([
            'name' => 'Mauri',
            'email' => 'maumenachotri224@gmail.com',
            'password' => bcrypt('contra123'),
            'id_rol' => 1,
            'dos_pasos_habilitado' => 1
        ]);
        User::create([
            'name' => 'Kiara',
            'email' => 'kiaritapino11@gmail.com',
            'password' => bcrypt('contra123'),
            'id_rol' => 1,
            'dos_pasos_habilitado' => 1
        ]);
        User::create([
            'name' => 'Evelyn',
            'email' => 'evelynburgoa04@gmail.com',
            'password' => bcrypt('contra123'),
            'id_rol' => 1,
            'dos_pasos_habilitado' => 1
        ]);
        User::create([
            'name' => 'Wawita',
            'email' => 'huanapacowara@gmail.com',
            'password' => bcrypt('contra123'),
            'id_rol' => 1,
            'dos_pasos_habilitado' => 1
        ]);
    }
}
