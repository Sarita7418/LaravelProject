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
        ]);
    }
}
