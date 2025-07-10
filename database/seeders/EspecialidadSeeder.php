<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadSeeder extends Seeder
{
    public function run()
    {
        DB::table('especialidades')->insert([
            ['nombre' => 'Medicina Forense'],
            ['nombre' => 'Biología'],
            ['nombre' => 'Química'],
            ['nombre' => 'Criminalística'],
            ['nombre' => 'Psicología Forense'],
            // Agrega las especialidades que necesites
        ]);
    }
}
