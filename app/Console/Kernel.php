<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define los comandos programados de la aplicación.
     */
   protected function schedule(Schedule $schedule): void
    {
        // Comando real
        $schedule->command('reportes:usuarios')->everyMinute()->withoutOverlapping();

        // 🔍 Bloque de prueba
        $schedule->call(function () {
            file_put_contents(storage_path('app/cron_test.txt'), now() . "\n", FILE_APPEND);
        })->everyMinute();
    }
    /**
     * Registra los comandos Artisan disponibles.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
