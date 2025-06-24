<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;

class NewPasswordController extends Controller
{
    /**
     * Maneja una solicitud de nueva contraseña.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'token' => ['required'],
            'correo' => ['required', 'email'],
            'contrasena' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $estado = Password::reset(
            $request->only('correo', 'contrasena', 'contrasena_confirmation', 'token'),
            function ($usuario) use ($request) {
                $usuario->forceFill([
                    'contrasena' => Hash::make($request->string('contrasena')),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($usuario));
            }
        );

        if ($estado != Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'correo' => [__($estado)],
            ]);
        }

        return response()->json(['estado' => __($estado)]);
    }
}
