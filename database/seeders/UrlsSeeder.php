<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class UrlsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('urls')->insert([
            [
                'ruta' => '/dashboard',
                'componente' => 'Dashboard',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ruta' => '/dashboard/admin',
                'componente' => 'Administracion',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ruta' => '/dashboard/admin/usuarios',
                'componente' => 'Usuarios',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'ruta' => '/dashboard/admin/roles',
                'componente' => 'Roles',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }
}
