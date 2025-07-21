<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;

class AccionesSeeder extends Seeder
{
    public function run(): void
    {
        $acciones = [
            ['nombre' => 'crear',   'descripcion' => 'Permite crear registros'],
            ['nombre' => 'editar',  'descripcion' => 'Permite editar registros'],
            ['nombre' => 'activar', 'descripcion' => 'Permite activar o desactivar registros'],
            ['nombre' => 'eliminar', 'descripcion' => 'Permite eliminar registros']
        ];

        foreach ($acciones as $accionData) {
            Accion::create($accionData);
        }
    }
}