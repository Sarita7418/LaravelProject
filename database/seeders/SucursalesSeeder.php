<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;
use App\Models\Empresa;

class SucursalesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Empresa::all() as $empresa) {

            $principal = Sucursal::create([
                'id_empresa' => $empresa->id,
                'nombre' => 'Sucursal Principal ' . $empresa->municipio,
                'codigo_sucursal' => strtoupper(substr($empresa->nombre_comercial, 0, 3)) . '-001',
                'direccion' => $empresa->direccion_fiscal,
                'telefono' => $empresa->telefono,
                'email' => 'principal@' . strtolower(str_replace(' ', '', $empresa->nombre_comercial)) . '.bo',
                'estado' => true,
                'id_sucursal_padre' => null,
                'id_representante_legal' => $empresa->id_representante_legal,
            ]);

            Sucursal::create([
                'id_empresa' => $empresa->id,
                'nombre' => 'Sub-Sucursal Comercial',
                'codigo_sucursal' => strtoupper(substr($empresa->nombre_comercial, 0, 3)) . '-002',
                'direccion' => 'Zona Comercial',
                'telefono' => $empresa->telefono,
                'email' => 'comercial@' . strtolower(str_replace(' ', '', $empresa->nombre_comercial)) . '.bo',
                'estado' => true,
                'id_sucursal_padre' => $principal->id,
                'id_representante_legal' => $empresa->id_representante_legal,
            ]);
        }
    }
}
