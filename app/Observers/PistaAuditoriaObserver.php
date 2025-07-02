<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use App\Models\PistaAuditoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class PistaAuditoriaObserver
{
    protected function registrar(Model $model, string $accion): void
    {
        try {
            PistaAuditoria::create([
                'fecha' => now(),
                'usuario_bd' => Auth::check() ? Auth::user()->name : 'system',
                'accion' => $accion,
                'nombre_host' => gethostname(),
                'ip_host' => Request::ip(),
                'pk' => 'id=' . $model->getKey(),
                'nombre_tabla' => $model->getTable(),
                //'codigo_usuario' => $model->cod_usuario ?? null,
                //'codigo_regional_usuario' => $model->regcod ?? null,
                'registros1' => $accion === 'INSERT' ? null : json_encode($model->getOriginal(), JSON_UNESCAPED_UNICODE),
                'registros2' => $accion === 'DELETE' ? null : json_encode($model->getAttributes(), JSON_UNESCAPED_UNICODE),
            ]);
        } catch (\Throwable $e) {
            // Opcional: puedes guardar errores de auditoría en logs si lo deseas
            logger()->error('Error al registrar auditoría: ' . $e->getMessage());
        }
    }

    public function created(Model $model): void
    {
        $this->registrar($model, 'INSERT');
    }

    public function updated(Model $model): void
    {
        $this->registrar($model, 'UPDATE');
    }

    public function deleted(Model $model): void
    {
        $this->registrar($model, 'DELETE');
    }
}
