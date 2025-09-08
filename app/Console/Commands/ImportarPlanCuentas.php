<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanCuentasImport;
use Illuminate\Support\Facades\DB;

class ImportarPlanCuentas extends Command
{
    protected $signature = 'import:plan-cuentas {--reset}';
    protected $description = 'Importa plan de cuentas desde Excel';

    public function handle()
    {
        $ruta = storage_path('imports/PLAN_CUENTAS.xlsx');
        
        if (!file_exists($ruta)) {
            $this->error("Archivo no encontrado en: {$ruta}");
            return 1;
        }

        if ($this->option('reset')) {
            DB::table('plan_cuentas')->truncate();
            $this->info('Tabla plan_cuentas limpiada');
        }

        $this->info('Procesando plan de cuentas... (Esto puede tomar unos minutos)');
        
        // Deshabilitar query log para mejor rendimiento
        DB::connection()->disableQueryLog();
        
        Excel::import(new PlanCuentasImport(), $ruta);
        
        $this->info("Importación completada. Registros totales: " . DB::table('plan_cuentas')->count());
        return 0;
    }
}