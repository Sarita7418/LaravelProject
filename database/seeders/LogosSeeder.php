<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logo;
use App\Models\Empresa;
use App\Models\Sucursal;

class LogosSeeder extends Seeder
{
    public function run(): void
    {
        $logos = [
            'PIL Andina' => 'pil_andina_logo.png',
            'Delizia'    => 'delizia_logo.png',
            'EBA'        => 'eba_logo.png',
            'SOBOCE'     => 'soboce_logo.png',
            'CBN'        => 'cbn_logo.png',
        ];

        foreach ($logos as $empresaNombre => $logoFile) {

            $empresa = Empresa::where('nombre_comercial', $empresaNombre)->first();
            if (!$empresa) continue;

            $logoPath = public_path("logos/$logoFile");
            if (!file_exists($logoPath)) continue;

            $binary = file_get_contents($logoPath);

            Logo::create([
                'id_entidad' => $empresa->id,
                'tipo_entidad' => 'empresa',
                'logo' => $binary,
            ]);

            $sucursal = Sucursal::where('id_empresa', $empresa->id)
                ->whereNull('id_sucursal_padre')
                ->first();

            if ($sucursal) {
                Logo::create([
                    'id_entidad' => $sucursal->id,
                    'tipo_entidad' => 'sucursal',
                    'logo' => $binary,
                ]);
            }
        }
    }
}
