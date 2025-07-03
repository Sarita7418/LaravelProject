<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
                'habilitado_2fa' => false,
            ],
            [
                'name' => 'Usuario',
                'email' => 'user@gmail.com',
                'password' => bcrypt('user123'),
                'id_rol' => 2,
                'estado' => 1,
                'habilitado_2fa' => false,
            ],
            [
                'name' => 'Mike',
                'email' => 'mg26667418@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
                'habilitado_2fa' => true,
            ],
            [
                'name' => 'Mauri',
                'email' => 'maumenachotri224@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
                'habilitado_2fa' => true,
            ],
            [
                'name' => 'Kiara',
                'email' => 'kiaritapino11@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
                'habilitado_2fa' => true,
            ],
            [
                'name' => 'Evelyn',
                'email' => 'evelynburgoa04@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
                'habilitado_2fa' => true,
            ],
            [
                'name' => 'Wawita',
                'email' => 'huanapacowara@gmail.com',
                'password' => bcrypt('contra123'),
                'id_rol' => 1,
                'estado' => 1,
                'habilitado_2fa' => true,
            ],
        ];

        foreach ($usuarios as $usuarioData) {
            $habilitado = $usuarioData['habilitado_2fa'];
            unset($usuarioData['habilitado_2fa']);

            $user = User::create($usuarioData);

            DB::table('codigos_verificacion')->insert([
                'usuario_id' => $user->id,
                'codigo' => null,
                'expira_en' => null,
                'habilitado' => $habilitado,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
