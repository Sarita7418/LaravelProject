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

        // ADMIN (ID 1) tiene crear, editar, activar en Usuarios (menu_id: x) y Roles (menu_id: y)
        $usuarios   = MenuItem::where('ruta', '/dashboard/usuarios')->first();
        $roles      = MenuItem::where('ruta', '/dashboard/roles')->first();

        $crear      = Accion::where('nombre', 'crear')->first();
        $editar     = Accion::where('nombre', 'editar')->first();
        $activar    = Accion::where('nombre', 'activar')->first();

        // Admin en Usuarios
        DB::table('accion_menu_item_rol')->insert([
            [
                'id_rol'       => 1,
                'id_menu_item' => $usuarios->id,
                'id_accion'    => $crear->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id_rol'       => 1,
                'id_menu_item' => $usuarios->id,
                'id_accion'    => $editar->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id_rol'       => 1,
                'id_menu_item' => $usuarios->id,
                'id_accion'    => $activar->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // Admin en Roles
        DB::table('accion_menu_item_rol')->insert([
            [
                'id_rol'       => 1,
                'id_menu_item' => $roles->id,
                'id_accion'    => $crear->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id_rol'       => 1,
                'id_menu_item' => $roles->id,
                'id_accion'    => $editar->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id_rol'       => 1,
                'id_menu_item' => $roles->id,
                'id_accion'    => $activar->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);

        // USER (ID 2) tiene solo crear y editar en Usuarios
        DB::table('accion_menu_item_rol')->insert([
            [
                'id_rol'       => 2,
                'id_menu_item' => $usuarios->id,
                'id_accion'    => $crear->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
            [
                'id_rol'       => 2,
                'id_menu_item' => $usuarios->id,
                'id_accion'    => $editar->id,
                'created_at'   => $now,
                'updated_at'   => $now,
            ],
        ]);
    }
}
