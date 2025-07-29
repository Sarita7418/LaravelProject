<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AreaImpacto;
use App\Models\Protocolo;

class AreaImpactoProtocoloSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
         // Relaciona aleatoriamente los primeros protocolos con áreas de impacto
        $protocolos = Protocolo::all();
        $areas = AreaImpacto::all();

        foreach ($protocolos as $protocolo) {
            // Asociar de 1 a 3 áreas aleatorias por protocolo
            $areaIds = $areas->random(rand(1, 3))->pluck('id')->toArray();
            $protocolo->areasImpacto()->syncWithoutDetaching($areaIds);
        }
    }
}
