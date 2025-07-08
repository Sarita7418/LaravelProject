<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;
use App\Models\AccionRol;

class AccionRolSeeder extends Seeder
{
    public function run(): void
    {
        $acciones = Accion::all();

        foreach ($acciones as $accion) {
            // Admin (ID 1) tiene todas
            AccionRol::create([
                'id_rol' => 1,
                'id_accion' => $accion->id,
            ]);

            // User (ID 2) solo crear y editar usuarios
            if (in_array($accion->nombre, ['crear_usuarios', 'editar_usuarios'])) {
                AccionRol::create([
                    'id_rol' => 2,
                    'id_accion' => $accion->id,
                ]);
            }
        }
    }
}
