<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Logo;
use App\Models\Empresa;
use App\Models\Sucursal;

class LogosSeeder extends Seeder
{
    public function run()
    {
        // Obtener la empresa con id = 1 (PIL Andina)
        $empresa = Empresa::find(1);

        // Leer el archivo de logo desde la carpeta public/logos
        $logoPath = public_path('logos/pil_andina_logo.png');  // Ruta del logo
        $logoBinary = file_get_contents($logoPath);  // Convertir el archivo a binario

        // Crear el logo para la empresa PIL Andina
        Logo::create([
            'id_entidad' => $empresa->id,
            'tipo_entidad' => 'empresa',
            'logo' => $logoBinary,  // Almacenamos el logo en binario
        ]);

        // Crear logo para una sucursal (por ejemplo, para la sucursal Cochabamba)
        $sucursal = Sucursal::where('id_empresa', 1)->first();  // Obtener la primera sucursal de la empresa PIL Andina

        Logo::create([
            'id_entidad' => $sucursal->id,
            'tipo_entidad' => 'sucursal',
            'logo' => $logoBinary,  // Usamos el mismo logo para la sucursal
        ]);
    }
}
