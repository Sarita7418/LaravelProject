<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubdominiosSeeder extends Seeder
{
    public function run()
    {
        DB::table('subdominios')->insert([
            ['id' => 1, 'id_dominio' => 1, 'descripcion' => 'judicial'],
            ['id' => 2, 'id_dominio' => 1, 'descripcion' => 'cientifico'],
            ['id' => 3, 'id_dominio' => 1, 'descripcion' => 'formativo'],
            ['id' => 4, 'id_dominio' => 2, 'descripcion' => 'borrador'],
            ['id' => 5, 'id_dominio' => 2, 'descripcion' => 'en_revision'],
            ['id' => 6, 'id_dominio' => 2, 'descripcion' => 'validado'],
            ['id' => 7, 'id_dominio' => 2, 'descripcion' => 'archivado'],
        ]);
    }
}