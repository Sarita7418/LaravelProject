<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PoliticoIneImport;
use Illuminate\Support\Facades\DB;

class ImportarPoliticos extends Command
{
    protected $signature = 'import:politico {--reset}';
    protected $description = 'Importa datos políticos desde Excel';

    public function handle()
    {
        $ruta = storage_path('imports/POLITICO.xlsx');
        
        if (!file_exists($ruta)) {
            $this->error(" Archivo no encontrado");
            return 1;
        }

        if ($this->option('reset')) {
            DB::statement('SET FOREIGN_KEY_CHECKS = 0;');
            DB::table('politicos_ubicacion')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS = 1;');
            
            $this->info(' Tabla limpiada exitosamente.');
        }

        $this->info(' Procesando datos... (Esto puede tomar unos minutos)');
        
        DB::connection()->disableQueryLog();
        
        Excel::import(new PoliticoIneImport(), $ruta);
        
        $this->info("\n Importación completada. Registros totales: ".DB::table('politicos_ubicacion')->count());
        return 0;
    }
}