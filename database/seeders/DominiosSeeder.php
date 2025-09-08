<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('dominios')->insert([
            ['id' => 1, 'descripcion' => 'estado_protocolo'],
            ['id' => 2, 'descripcion' => 'tipo_cuenta'],
            ['id' => 3, 'descripcion' => 'grupo_estado_financiero'],
            ['id' => 4, 'descripcion' => 'cuenta_ajuste_presupuesto'],
        ]);
    }
}
