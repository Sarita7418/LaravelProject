<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MedicamentosSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         DB::table('medicamentos')->insert([
            ['nombre' => 'Diazepam', 'es_controlado' => true],
            ['nombre' => 'Paracetamol', 'es_controlado' => false],
            ['nombre' => 'Clonazepam', 'es_controlado' => true],
            ['nombre' => 'Ibuprofeno', 'es_controlado' => false],
        ]);
    }
}
