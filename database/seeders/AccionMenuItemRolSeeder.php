<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\MenuItem;
use App\Models\Accion;

class AccionMenuItemRolSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $adminId = 1;
        $userId = 2;

        $menuUsuarios   = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $menuRoles      = MenuItem::where('ruta', '/dashboard/roles')->first();
        $menuPersonas   = MenuItem::where('ruta', '/dashboard/personas')->first();
        $menuProtocolos = MenuItem::where('ruta', '/dashboard/protocolos')->first();

        $acciones = Accion::all();

        // ADMIN - Todas las acciones en todos los menús
        foreach ([$menuUsuarios, $menuRoles, $menuPersonas, $menuProtocolos] as $menu) {
            foreach ($acciones as $accion) {
                DB::table('accion_menu_item_rol')->updateOrInsert(
                    [
                        'id_rol'       => $adminId,
                        'id_menu_item' => $menu->id,
                        'id_accion'    => $accion->id,
                    ],
                    [
                        'created_at'   => $now,
                        'updated_at'   => $now,
                    ]
                );
            }
        }

        // USER - Crear y editar en Usuarios
        DB::table('accion_menu_item_rol')->updateOrInsert(
            [
                'id_rol'       => $userId,
                'id_menu_item' => $menuUsuarios->id,
                'id_accion'    => Accion::where('nombre', 'crear')->first()->id,
            ],
            [
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );

        DB::table('accion_menu_item_rol')->updateOrInsert(
            [
                'id_rol'       => $userId,
                'id_menu_item' => $menuUsuarios->id,
                'id_accion'    => Accion::where('nombre', 'editar')->first()->id,
            ],
            [
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );

        // USER - Editar en Personas
        DB::table('accion_menu_item_rol')->updateOrInsert(
            [
                'id_rol'       => $userId,
                'id_menu_item' => $menuPersonas->id,
                'id_accion'    => Accion::where('nombre', 'editar')->first()->id,
            ],
            [
                'created_at'   => $now,
                'updated_at'   => $now,
            ]
        );
    }
}
