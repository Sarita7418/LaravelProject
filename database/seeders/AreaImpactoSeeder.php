<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB facade for database operations
use App\Models\AreaImpacto;

class AreaImpactoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $areas = [
            ['nombre' => 'Salud', 'descripcion' => 'Impacto en el área de salud.'],
            ['nombre' => 'Educación', 'descripcion' => 'Impacto en el área educativa.'],
            ['nombre' => 'Medio Ambiente', 'descripcion' => 'Impacto en el medio ambiente.'],
        ];

        foreach ($areas as $area) {
            AreaImpacto::create($area);
        }
    }
}
