<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sucursal;
use App\Models\Empresa;  // Importamos el modelo Empresa

class SucursalesSeeder extends Seeder
{
    public function run()
    {
        // Obtener la empresa con id = 1 (PIL Andina)
        $empresa = Empresa::find(1);

        // Crear sucursal departamental para PIL Andina
        $sucursalDepartamental = Sucursal::create([
            'id_empresa' => $empresa->id,
            'nombre' => 'Sucursal Cochabamba',
            'codigo_sucursal' => 'SCOCH001',
            'direccion' => 'Av. Panamericana, Cochabamba',
            'telefono' => '+591 4 4605001',
            'email' => 'cochabamba@pilandina.com.bo',
            'estado' => true,
            'id_sucursal_padre' => null, // No tiene sucursal madre, es la sucursal principal
            'id_representante_legal' => 2, // Asignar el representante legal (Persona con id=2)
        ]);

        // Crear una sub-sucursal para la sucursal departamental de PIL Andina
        Sucursal::create([
            'id_empresa' => $empresa->id,
            'nombre' => 'Sub-Sucursal Cochabamba 1',
            'codigo_sucursal' => 'SSCOCH001',
            'direccion' => 'Av. Central, Cochabamba',
            'telefono' => '+591 4 4605002',
            'email' => 'subsucursal1@pilandina.com.bo',
            'estado' => true,
            'id_sucursal_padre' => $sucursalDepartamental->id,  // Apunta a la sucursal departamental
            'id_representante_legal' => 3, // Asignar otro representante legal (Persona con id=3)
        ]);
    }
}
