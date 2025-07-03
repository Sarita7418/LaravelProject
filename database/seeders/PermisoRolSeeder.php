<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permiso;
use Illuminate\Support\Carbon;

class PermisoRolSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Role::where('descripcion', 'admin')->first();
        $user = Role::where('descripcion', 'user')->first();

        $permisos = Permiso::with('menuItem')->get();

        if ($admin) {
            $admin->permisos()->attach(
                $permisos->pluck('id')->toArray(),
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }

        if ($user) {
            $soloDashboard = $permisos->filter(fn($p) => $p->menuItem->item === 'Dashboard');
            $user->permisos()->attach(
                $soloDashboard->pluck('id')->toArray(),
                ['created_at' => Carbon::now(), 'updated_at' => Carbon::now()]
            );
        }
    }
}
