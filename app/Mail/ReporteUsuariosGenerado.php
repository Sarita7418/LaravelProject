<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReporteUsuariosGenerado extends Mailable
{
    use Queueable, SerializesModels;

    public $pathArchivo;

    public function __construct($pathArchivo)
    {
        $this->pathArchivo = $pathArchivo;
    }

    public function build()
    {
        return $this->subject('📊 Reporte automático de usuarios')
            ->view('emails.reporte_usuarios')
            ->attach(storage_path('app/' . $this->pathArchivo));
    }
}
