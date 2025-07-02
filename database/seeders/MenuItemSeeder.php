<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;
use App\Models\Url;
use App\Models\Role;
use Illuminate\Support\Carbon;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener URLs ya creadas desde UrlsSeeder
        $urlDashboard = Url::where('ruta', '/dashboard')->first();
        $urlUsuariosAdmin = Url::where('ruta', '/admin/usuarios')->first();
        $urlRolesAdmin = Url::where('ruta', '/admin/roles')->first();
        $urlAdmin = Url::where('ruta', '/admin')->first();

        // Menús jerárquicos
        $menu1 = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'Administración',
            'id_url' => null,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu2 = MenuItem::create([
            'id_padre' => $menu1->id,
            'nivel' => 2,
            'item' => 'Usuarios',
            'id_url' => optional($urlUsuariosAdmin)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu3 = MenuItem::create([
            'id_padre' => $menu1->id,
            'nivel' => 2,
            'item' => 'Roles',
            'id_url' => optional($urlRolesAdmin)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu4 = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'Dashboard',
            'id_url' => optional($urlDashboard)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu5 = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'DashboardAdmin',
            'id_url' => optional($urlAdmin)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Buscar los roles
        $adminRole = Role::where('descripcion', 'admin')->first();
        $userRole = Role::where('descripcion', 'user')->first();

        // Relacionar menús con roles
        if ($adminRole) {
            $adminRole->permisos()->attach([
                $menu1->id,
                $menu2->id,
                $menu3->id,
                $menu4->id,
                $menu5->id
            ], [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        if ($userRole) {
            $userRole->permisos()->attach([$menu4->id], [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
