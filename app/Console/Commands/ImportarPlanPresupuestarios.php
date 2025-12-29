<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanPresupuestariosImport;
use Illuminate\Support\Facades\DB;

class ImportarPlanPresupuestarios extends Command
{
    protected $signature = 'import:plan-presupuestarios {--reset}';
    protected $description = 'Importa plan presupuestarios desde Excel';

    public function handle()
    {
        $ruta = storage_path('imports/PLAN_PRESUPUESTARIOS.xlsx');
        
        if (!file_exists($ruta)) {
            $this->error("Archivo no encontrado en: {$ruta}");
            return 1;
        }

        if ($this->option('reset')) {
            DB::table('plan_presupuestarios')->truncate();
            $this->info('Tabla plan_presupuestarios limpiada');
        }

        $this->info('Procesando plan presupuestarios... (Esto puede tomar unos minutos)');
        
        // Deshabilitar query log para mejor rendimiento
        DB::connection()->disableQueryLog();
        
        Excel::import(new PlanPresupuestariosImport(), $ruta);
        
        $this->info("Importación completada. Registros totales: " . DB::table('plan_presupuestarios')->count());
        return 0;
    }
}