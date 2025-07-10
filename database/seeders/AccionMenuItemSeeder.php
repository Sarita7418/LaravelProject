<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;
use App\Models\MenuItem;

class AccionMenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Obtén todas las acciones
        $crear   = Accion::where('nombre', 'crear')->first();
        $editar  = Accion::where('nombre', 'editar')->first();
        $activar = Accion::where('nombre', 'activar')->first();

        // Obtén los menú items principales
        $usuarios   = MenuItem::where('item', 'Usuarios')->first();
        $roles      = MenuItem::where('item', 'Roles')->first();
        $protocolos = MenuItem::where('item', 'Protocolos')->first();

        // Asociar acciones a Usuarios (admin: todas, user: solo crear y editar)
        if ($usuarios) {
            $usuarios->acciones()->sync([$crear->id, $editar->id, $activar->id]); // Admin tendrá acceso a todo por relación de rol a menú
        }

        // Asociar acciones a Roles (solo admin)
        if ($roles) {
            $roles->acciones()->sync([$crear->id, $editar->id, $activar->id]);
        }

        // Asociar acciones a Protocolos (admin: todas, user: solo crear y editar)
        if ($protocolos) {
            $protocolos->acciones()->sync([$crear->id, $editar->id, $activar->id]);
        }
    }
}
