<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\CodigoVerificacion;
use Carbon\Carbon;

class AutenticacionDosPasosController extends Controller
{
    public function enviarCodigo(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $codigo = rand(100000, 999999);

        CodigoVerificacion::updateOrCreate(
            ['usuario_id' => $usuario->id],
            [
                'codigo' => $codigo,
                'expira_en' => Carbon::now()->addMinutes(10),
                'habilitado' => false
            ]
        );

        Mail::raw("Tu código de verificación es: {$codigo}", function ($message) use ($usuario) {
            $message->to($usuario->email)
                    ->subject('Código de Verificación - Autenticación de Dos Pasos');
        });

        return response()->json([
            'mensaje' => 'Código enviado al correo electrónico',
            'correo_parcial' => $this->ocultarCorreo($usuario->email)
        ]);
    }

    public function verificarCodigo(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|size:6'
        ]);

        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $registro = CodigoVerificacion::where('usuario_id', $usuario->id)->first();

        if (!$registro || !$registro->codigo || Carbon::now()->gt($registro->expira_en)) {
            return response()->json(['error' => 'Código expirado o inválido'], 400);
        }

        if ($registro->codigo !== $request->codigo) {
            return response()->json(['error' => 'Código incorrecto'], 400);
        }

        $registro->update([
            'codigo' => null,
            'expira_en' => null,
            'habilitado' => true
        ]);

        $usuario = User::with('role')->find($usuario->id);

        return response()->json([
            'mensaje' => 'Código verificado correctamente',
            'usuario' => $usuario,
            'rol' => $usuario->role ? $usuario->role->descripcion : null
        ]);
    }

    public function habilitarDosPasos(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        CodigoVerificacion::updateOrCreate(
            ['usuario_id' => $usuario->id],
            ['habilitado' => true]
        );

        return response()->json(['mensaje' => 'Autenticación de dos pasos habilitada']);
    }

    public function deshabilitarDosPasos(Request $request)
    {
        $usuario = Auth::user();
        if (!$usuario) {
            return response()->json(['error' => 'No autenticado.'], 401);
        }

        $registro = CodigoVerificacion::where('usuario_id', $usuario->id)->first();

        if ($registro) {
            $registro->update([
                'habilitado' => false,
                'codigo' => null,
                'expira_en' => null
            ]);
        }

        return response()->json(['mensaje' => 'Autenticación de dos pasos deshabilitada']);
    }

    private function ocultarCorreo($email)
    {
        $partes = explode('@', $email);
        $nombre = $partes[0];
        $dominio = $partes[1];

        if (strlen($nombre) <= 2) {
            return '*' . substr($nombre, -1) . '@' . $dominio;
        }

        return substr($nombre, 0, 2) . str_repeat('*', strlen($nombre) - 2) . '@' . $dominio;
    }
}
