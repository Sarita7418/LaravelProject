<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisosRolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permiso_rol')->insert([
            [
                'rol_id' => 1, // admin
                'permiso_id' => 10, // admin_panel
                  'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'rol_id' => 2, // user
                'permiso_id' => 9, // ver_dashboard
               'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            
        ]);
    }
}
