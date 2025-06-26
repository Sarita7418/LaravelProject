<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PermisoRolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('permiso_rol')->insert([
            [
                'rol_id' => 1, // admin
                'permiso_id' => 19, // admin_panel
                'created_at' => Carbon::create('2025', '06', '26', '15', '50', '36'),
                'updated_at' => Carbon::create('2025', '06', '26', '15', '50', '36'),
            ],
            [
                'rol_id' => 2, // user
                'permiso_id' => 18, // ver_dashboard
                'created_at' => Carbon::create('2025', '06', '26', '14', '08', '15'),
                'updated_at' => Carbon::create('2025', '06', '26', '14', '08', '15'),
            ],
            
        ]);
    }
}
