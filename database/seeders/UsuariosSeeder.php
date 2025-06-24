<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Usuario;

class UsuariosSeeder extends Seeder
{
    public function run(): void
    {
        Usuario::create([
            'nombre' => 'Admin',
            'correo' => 'admin@gmail.com',
            'contrasena' => bcrypt('admin123'),
            'rol_id' => 1
        ]);

        Usuario::create([
            'nombre' => 'Usuario',
            'correo' => 'user@gmail.com',
            'contrasena' => bcrypt('user123'),
            'rol_id' => 2
        ]);
    }
}
