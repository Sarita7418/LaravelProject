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
        $url1 = Url::firstOrCreate(
    ['ruta' => '/dashboard'],
    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
);
    $url2 = Url::firstOrCreate(
    ['ruta' => '/usuarios'],
    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
);

$url3 = Url::firstOrCreate(
    ['ruta' => '/roles'],
    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
);

$url4 = Url::firstOrCreate(
    ['ruta' => '/admin'],
    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
);

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
            'id_url' => $url2->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu3 = MenuItem::create([
            'id_padre' => $menu1->id,
            'nivel' => 2,
            'item' => 'Roles',
            'id_url' => $url3->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        $menu4 = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'Dashboard',
            'id_url' => $url1->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

         $menu5 = MenuItem::create([
            'id_padre' => null,
            'nivel' => 1,
            'item' => 'DashboardAdmin',
            'id_url' => $url4->id,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ]);

        // Buscar los roles
        $adminRole = Role::where('descripcion', 'admin')->first();
        $userRole = Role::where('descripcion', 'user')->first();

        // Relacionar menús con roles
        if ($adminRole) {
            $adminRole->permisos()->attach([$menu1->id, $menu2->id, $menu3->id, $menu4->id, $menu5->id ], [
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
