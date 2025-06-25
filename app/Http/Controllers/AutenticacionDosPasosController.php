<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use Carbon\Carbon;

class AutenticacionDosPasosController extends Controller
{
    public function enviarCodigo(Request $request)
    {
        $usuario = Auth::user();
        $codigo = rand(100000, 999999);
        
        // Guardar código con expiración de 10 minutos
        $usuario->update([
            'codigo_verificacion' => $codigo,
            'codigo_expira_en' => Carbon::now()->addMinutes(10)
        ]);

        // Enviar código por correo
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
        
        // Verificar si el código existe y no ha expirado
        if (!$usuario->codigo_verificacion || 
            Carbon::now()->gt($usuario->codigo_expira_en)) {
            return response()->json([
                'error' => 'Código expirado o inválido'
            ], 400);
        }

        // Verificar si el código coincide
        if ($usuario->codigo_verificacion !== $request->codigo) {
            return response()->json([
                'error' => 'Código incorrecto'
            ], 400);
        }

        // Limpiar código después de verificación exitosa
        $usuario->update([
            'codigo_verificacion' => null,
            'codigo_expira_en' => null
        ]);

        return response()->json([
            'mensaje' => 'Código verificado correctamente',
            'usuario' => $usuario->load('role'),
            'rol' => $usuario->role?->descripcion
        ]);
    }

    public function habilitarDosPasos(Request $request)
    {
        $usuario = Auth::user();
        $usuario->update(['dos_pasos_habilitado' => true]);

        return response()->json([
            'mensaje' => 'Autenticación de dos pasos habilitada'
        ]);
    }

    public function deshabilitarDosPasos(Request $request)
    {
        $usuario = Auth::user();
        $usuario->update(['dos_pasos_habilitado' => false]);

        return response()->json([
            'mensaje' => 'Autenticación de dos pasos deshabilitada'
        ]);
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