<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Persona;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            [
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'rol' => 1,
                'nombres' => 'Angel David',
                'apellido_paterno' => 'Revilla',
                'apellido_materno' => 'Dross',
                'ci' => '12345678',
                'telefono' => '77712345',
            ],
            [
                'name' => 'Usuario',
                'email' => 'user@gmail.com',
                'rol' => 2,
                'nombres' => 'Jordi ENP',
                'apellido_paterno' => 'Wild',
                'apellido_materno' => 'Noloses',
                'ci' => '23456789',
                'telefono' => '77723456',
            ],
            [
                'name' => 'SaritaMushasha123',
                'email' => 'mg26667418@gmail.com',
                'rol' => 1,
                'nombres' => 'Jhovanni Michael',
                'apellido_paterno' => 'Gutierrez',
                'apellido_materno' => 'Jimenez',
                'ci' => '34567890',
                'telefono' => '77734567',
            ],
            [
                'name' => 'MochiCausaGaaa',
                'email' => 'maumenachotri224@gmail.com',
                'rol' => 1,
                'nombres' => 'Mauricio David',
                'apellido_paterno' => 'Menacho',
                'apellido_materno' => 'Triguero',
                'ci' => '45678901',
                'telefono' => '77745678',
            ],
             [
                'name' => 'JoseRoberto',
                'email' => 'jannyrobertooo224@gmail.com',
                'rol' => 1,
                'nombres' => 'Jose Roberto',
                'apellido_paterno' => 'Macochapi',
                'apellido_materno' => 'Suarez',
                'ci' => '48946416',
                'telefono' => '4894941616',
            ],
            [
                'name' => 'PianaKiro',
                'email' => 'kiaritapino11@gmail.com',
                'rol' => 1,
                'nombres' => 'Kiara Carmen',
                'apellido_paterno' => 'Pino',
                'apellido_materno' => 'Morales',
                'ci' => '56789012',
                'telefono' => '77756789',
            ],
            [
                'name' => 'Evelyx',
                'email' => 'evelynburgoa04@gmail.com',
                'rol' => 1,
                'nombres' => 'Evelyn Cristina',
                'apellido_paterno' => 'Burgoa',
                'apellido_materno' => 'Aliaga',
                'ci' => '67890123',
                'telefono' => '77767890',
            ],
            [
                'name' => 'Wawita',
                'email' => 'huanapacowara@gmail.com',
                'rol' => 1,
                'nombres' => 'Wara Rocio',
                'apellido_paterno' => 'Huañapaco',
                'apellido_materno' => 'Laura',
                'ci' => '78901234',
                'telefono' => '77778901',
            ],
        ];

        foreach ($usuarios as $data) {
            $persona = Persona::create([
                'nombres' => $data['nombres'],
                'apellido_paterno' => $data['apellido_paterno'],
                'apellido_materno' => $data['apellido_materno'],
                'ci' => $data['ci'],
                'telefono' => $data['telefono'],
                'fecha_nacimiento' => '2000-01-01', // puedes variar si gustas
                'estado' => 1,
            ]);

            User::create([
                'id_persona' => $persona->id,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => bcrypt('contra123'),
                'id_rol' => $data['rol'],
                'estado' => 1,
            ]);
        }
    }
}
