<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LinameImport;
use App\Models\ImportacionLinameRaw;
use App\Models\ProductoGenerico;
use App\Models\MedicamentoLiname;
use Illuminate\Support\Facades\DB;

class ProcesarLiname extends Command
{
    protected $signature = 'liname:procesar';
    protected $description = 'Importa el Excel y normaliza la base de datos LINAME';

    public function handle()
    {
        $this->info('Iniciando proceso...');

        // 1. Limpiar tabla temporal previa
        $this->info('Limpiando tabla temporal...');
        ImportacionLinameRaw::truncate();

        // 2. Importar Excel
        $path = storage_path('imports/liname_import.xlsx'); // <--- RUTA CORREGIDA
        
        $this->info("Buscando archivo en: $path");

        if (!file_exists($path)) {
            $this->error("¡ERROR! No encuentro el archivo.");
            $this->error("Asegúrate de que esté en: " . $path);
            return;
        }

        try {
            // Pasamos null como disco para poder usar la ruta absoluta del sistema
            Excel::import(new LinameImport, $path, null, \Maatwebsite\Excel\Excel::XLSX);
        } catch (\Exception $e) {
            $this->error('Error leyendo el archivo: ' . $e->getMessage());
            return;
        }

        // 3. Normalizar
        $raws = ImportacionLinameRaw::all();
        $total = $raws->count();
        $bar = $this->output->createProgressBar($total);
        
        $this->info("\nNormalizando $total registros...");
        $bar->start();

        foreach ($raws as $raw) {
            // A. Buscar IDs de Clasificación (Padre e Hijo)
            // Buscamos el grupo (Ej: 'A')
            $grupo = DB::table('clasificaciones_liname')
                        ->where('codigo', $raw->grupo_co)
                        ->where('nivel', 1)
                        ->first();

            if (!$grupo) {
                // Si falla, registramos error y seguimos
                $this->error("\nGrupo no encontrado: " . $raw->grupo_co);
                continue;
            }

            // Buscamos el subgrupo (Ej: '01') que sea hijo de ese grupo
            $subgrupo = DB::table('clasificaciones_liname')
                        ->where('codigo', $raw->subgrupo_di)
                        ->where('padre_id', $grupo->id)
                        ->first();
            
            // Si no encuentra el subgrupo, usamos el ID del grupo padre por seguridad 
            // (aunque lo ideal es que siempre exista el subgrupo)
            $clasificacionId = $subgrupo ? $subgrupo->id : $grupo->id;

            // B. Crear o Buscar el Genérico (Evitamos repetidos)
            $generico = ProductoGenerico::firstOrCreate(
                ['nombre' => trim($raw->medicamento_nombre)], // Busca por nombre exacto
                ['codigo_atq' => $raw->codigo_atq] // Si no existe, lo crea con este ATQ
            );

            // C. Crear el Medicamento Final
            // Usamos updateOrCreate para no duplicar si corres el comando 2 veces
            MedicamentoLiname::updateOrCreate(
                ['codigo_completo' => $raw->codigo_completo], // Buscamos por código único A1102
                [
                    'clasificacion_id'     => $clasificacionId,
                    'producto_generico_id' => $generico->id,
                    'correlativo_go'       => $raw->correlativo_go,
                    'forma_farmaceutica'   => $raw->forma,
                    'concentracion'        => $raw->concentracion,
                    'uso_restringido'      => $raw->uso_restringido,
                ]
            );

            $bar->advance();
        }

        $bar->finish();
        $this->info("\n\n¡Proceso terminado con éxito! Base de datos lista.");
    }
}