<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;

class AccionesSeeder extends Seeder
{
    public function run(): void
    {
        $acciones = [
            ['nombre' => 'crear_usuarios', 'descripcion' => 'Permite crear usuarios'],
            ['nombre' => 'editar_usuarios', 'descripcion' => 'Permite editar usuarios'],
            ['nombre' => 'activar_usuarios', 'descripcion' => 'Permite activar o desactivar usuarios'],

            ['nombre' => 'crear_roles', 'descripcion' => 'Permite crear roles'],
            ['nombre' => 'editar_roles', 'descripcion' => 'Permite editar roles'],
            ['nombre' => 'activar_roles', 'descripcion' => 'Permite activar o desactivar roles'],

            ['nombre' => 'crear_personas', 'descripcion' => 'Permite crear personas'],
            ['nombre' => 'editar_personas', 'descripcion' => 'Permite editar personas'],
            ['nombre' => 'activar_personas', 'descripcion' => 'Permite activar o desactivar personas'],

            ['nombre' => 'crear_protocolos', 'descripcion' => 'Permite crear protocolos'],
            ['nombre' => 'editar_protocolos', 'descripcion' => 'Permite editar protocolos'],
            ['nombre' => 'activar_protocolos', 'descripcion' => 'Permite activar o desactivar protocolos'],
        ];

        foreach ($acciones as $accionData) {
            Accion::create($accionData);
        }
    }
}
