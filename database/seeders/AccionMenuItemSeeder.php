<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Accion;
use App\Models\MenuItem;
use Illuminate\Support\Facades\DB;

class AccionMenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $rutas = [
            '/dashboard/usuarios',
            '/dashboard/roles',
            '/dashboard/personas',
            '/dashboard/protocolos'
        ];

        $menus = MenuItem::whereIn('ruta', $rutas)->get();

        $acciones = Accion::whereIn('nombre', [
            'crear', 'editar', 'activar'
        ])->get();

        foreach ($menus as $menu) {
            foreach ($acciones as $accion) {
                DB::table('accion_menu_item')->updateOrInsert(
                    [
                        'id_menu_item' => $menu->id,
                        'id_accion'    => $accion->id
                    ],
                    [
                        'created_at' => now(),
                        'updated_at' => now()
                    ]
                );
            }
        }
    }
}
