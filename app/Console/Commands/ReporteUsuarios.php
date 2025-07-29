<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Exports\ExportarUsuarios;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

use Illuminate\Support\Facades\Mail;
use App\Mail\ReporteUsuariosGenerado;

use App\Models\ReporteGenerado;


class ReporteUsuarios extends Command
{
    protected $signature = 'reportes:usuarios';
    protected $description = 'Genera un Excel de usuarios creados ayer y lo guarda en storage';

   public function handle()
    {
        \Log::info("⚙️ Cron ejecutado: intentando generar reporte");

        $ayer = Carbon::yesterday()->toDateString();
        $hoy = Carbon::today()->toDateString();

        $usuarios = User::with(['persona', 'role'])
            ->whereDate('created_at', '>=', $ayer)
            ->whereDate('created_at', '<', $hoy)
            ->get();

        $nombreArchivo = 'usuarios-' . $hoy . '.xlsx';

        $this->info("Cantidad de usuarios filtrados: " . $usuarios->count());

        // Generar y guardar el Excel
        $guardado = Excel::store(new ExportarUsuarios($usuarios), 'reportes/' . $nombreArchivo);

                
        if (!$guardado) {
            $this->error("❌ No se pudo guardar el archivo");
        } else {
            $this->info("✅ Reporte guardado en: " . storage_path("app/private/reportes/$nombreArchivo"));

            // Registro del historial
            ReporteGenerado::create([
                'nombre_reporte' => 'usuarios',
                'formato' => 'xlsx',
                'rango_desde' => $ayer,
                'rango_hasta' => $hoy,
                'user_id' => null, // más adelante podés pasar el ID del usuario que ejecuta
                'fecha_generacion' => now(),
                'path_archivo' => "private/reportes/$nombreArchivo",
            ]);
      
                $admins = User::whereHas('role', function ($q) {
                    $q->where('descripcion', 'admin');
                })->get();

                foreach ($admins as $admin) {
                    Mail::to($admin->email)->send(new ReporteUsuariosGenerado("private/reportes/$nombreArchivo"));
                }
        }
    }

}
