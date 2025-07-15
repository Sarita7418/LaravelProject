<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade for database operations

class AreaImpactoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('area_impacto')->insert([
        ['nombre' => 'Tecnología', 'descripcion' => 'Impacto relacionado con tecnología'],
        ['nombre' => 'Salud', 'descripcion' => 'Impacto relacionado con salud'],
        ['nombre' => 'Educación', 'descripcion' => 'Impacto relacionado con educación'],
        ['nombre' => 'Medio Ambiente', 'descripcion' => 'Impacto relacionado con medio ambiente'],
        ['nombre' => 'Economía', 'descripcion' => 'Impacto relacionado con economía'],
        ['nombre' => 'Cultura', 'descripcion' => 'Impacto relacionado con cultura'],
        ['nombre' => 'Social', 'descripcion' => 'Impacto relacionado con aspectos sociales'],
        ['nombre' => 'Política', 'descripcion' => 'Impacto relacionado con política']        
    ]);
    }
}
