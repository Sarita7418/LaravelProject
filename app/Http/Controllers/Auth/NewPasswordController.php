<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Models\User;
use App\Models\CodigoVerificacion;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;


class NewPasswordController extends Controller
{
    public function enviarCodigoReset(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        // Eliminar códigos previos
        CodigoVerificacion::where('usuario_id', $user->id)->delete();

        $codigo = rand(100000, 999999);

        CodigoVerificacion::create([
            'usuario_id' => $user->id,
            'codigo' => $codigo,
            'expira_en' => Carbon::now()->addMinutes(10),
            'habilitado' => true
        ]);

        Mail::raw("Tu código para restablecer contraseña es: {$codigo}", function ($message) use ($user) {
            $message->to($user->email)
                    ->subject('Código para restablecer contraseña');
        });

        return response()->json([
            'message' => 'Código enviado al correo electrónico',
            'correo_parcial' => $this->ocultarCorreo($user->email)
        ]);
    }

    public function verificarCodigoReset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'codigo' => 'required|string|size:6'
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $registro = CodigoVerificacion::where('usuario_id', $user->id)
            ->where('codigo', $request->codigo)
            ->first();

        if (!$registro || Carbon::now()->gt($registro->expira_en)) {
            return response()->json(['error' => 'Código expirado o inválido'], 400);
        }

        $registro->delete();

        return response()->json(['message' => 'Código verificado correctamente']);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        return response()->json(['message' => 'Contraseña cambiada correctamente']);
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