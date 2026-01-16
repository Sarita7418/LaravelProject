<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\ProductoComercial;
use App\Models\ProductoGenerico;

class ImportarProductosComerciales extends Command
{
    protected $signature = 'import:ventali';
    protected $description = 'Importa productos comerciales desde storage/imports/productos_ventali.csv';

    public function handle()
{
    $path = storage_path('imports/productos_ventali.csv');

    if (!file_exists($path)) {
        $this->error("No encuentro el archivo.");
        return;
    }

    $this->info("Iniciando importación V3 (Búsqueda Bidireccional)...");

    $file = fopen($path, 'r');
    fgetcsv($file, 0, ";"); // Saltar encabezados

    $count = 0;
    $nuevos = 0;
    $noEncontradosEjemplos = [];

    DB::beginTransaction();

    try {
        while (($row = fgetcsv($file, 0, ";")) !== FALSE) {
            
            $nombreComercial = trim($row[2]);
            // Limpiamos el nombre del Excel
            $genericoExcelRaw = trim($row[3]); 

            if (empty($nombreComercial)) continue;

            // 1. LIMPIEZA EXCEL: Mayúsculas con acentos (mb_) y cortar compuestos
            $genericoLimpio = mb_strtoupper($genericoExcelRaw, 'UTF-8');
            
            // Cortamos en ; o + (Ej: "PARACETAMOL; CAFEINA" -> "PARACETAMOL")
            $partes = preg_split('/[;+]/', $genericoLimpio); 
            $nombreBusqueda = trim($partes[0]); 

            // 2. CONSULTA "TODO TERRENO"
            $generico = ProductoGenerico::query()
                // A) Coincidencia Exacta (Ignorando espacios en la BD)
                ->whereRaw("TRIM(UPPER(nombre)) = ?", [$nombreBusqueda])
                
                // B) Que el Excel contenga a la BD (Ej: Excel="PARACETAMOL 500", BD="PARACETAMOL")
                ->orWhereRaw("? LIKE CONCAT('%', TRIM(UPPER(nombre)), '%')", [$nombreBusqueda])
                
                // C) Que la BD contenga al Excel (Ej: Excel="PARACETAMOL", BD="PARACETAMOL (ACETAMINOFEN)")
                // ¡Esta es la que faltaba para el caso de tu imagen!
                ->orWhere('nombre', 'LIKE', "%{$nombreBusqueda}%")
                
                ->first();

            ProductoComercial::updateOrCreate(
                ['nro_registro_sanitario' => trim($row[1])],
                [
                    'nombre_comercial'       => $nombreComercial,
                    'producto_generico_id'   => $generico ? $generico->id : null,
                    'forma_farmaceutica'     => trim($row[4]),
                    'concentracion'          => trim($row[5]),
                    'laboratorio_fabricante' => trim($row[6]),
                    'empresa_proveedora'     => trim($row[10] ?? ''),
                    'pais_origen'            => trim($row[9] ?? ''),
                ]
            );

            $count++;
            if ($generico) {
                $nuevos++;
            } else {
                if (count($noEncontradosEjemplos) < 5) {
                    $noEncontradosEjemplos[] = $nombreBusqueda; 
                }
            }
            
            if ($count % 100 == 0) $this->info("Procesados: $count...");
        }

        DB::commit();
        
        $this->newLine();
        $this->info("=== RESULTADO V3 ===");
        $this->info("Total procesados: $count");
        $this->info("✅ Enlazados: $nuevos");

        

    } catch (\Exception $e) {
        DB::rollBack();
        $this->error("Error: " . $e->getMessage());
    }

    fclose($file);
}
}