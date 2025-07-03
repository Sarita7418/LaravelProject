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
        // Obtener URLs
        $urlDashboard = Url::where('ruta', '/dashboard')->first();
        $urlAdmin = Url::where('ruta', '/dashboard/administracion')->first();
        $urlUsuarios = Url::where('ruta', '/dashboard/administracion/usuarios')->first();
        $urlRoles = Url::where('ruta', '/dashboard/administracion/roles')->first();

        // Nivel 1
        $dashboardMenu = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'Dashboard',
            'id_url' => optional($urlDashboard)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Nivel 2
        $adminMenu = MenuItem::create([
            'id_padre' => $dashboardMenu->id,
            'nivel' => 2,
            'item' => 'Administración',
            'id_url' => optional($urlAdmin)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Nivel 3
        $usuariosMenu = MenuItem::create([
            'id_padre' => $adminMenu->id,
            'nivel' => 3,
            'item' => 'Usuarios',
            'id_url' => optional($urlUsuarios)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $rolesMenu = MenuItem::create([
            'id_padre' => $adminMenu->id,
            'nivel' => 3,
            'item' => 'Roles',
            'id_url' => optional($urlRoles)->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Roles
        $adminRole = Role::where('descripcion', 'admin')->first();
        $userRole = Role::where('descripcion', 'user')->first();

        if ($adminRole) {
            $adminRole->permisos()->attach([
                $dashboardMenu->id,
                $adminMenu->id,
                $usuariosMenu->id,
                $rolesMenu->id,
            ], [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }

        if ($userRole) {
            $userRole->permisos()->attach([$dashboardMenu->id], [
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
        }
    }
}
