<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PasswordResetLinkController extends Controller
{
    /**
     * Maneja una solicitud de enlace para restablecer contraseña.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'correo' => ['required', 'email'],
        ]);

        $estado = Password::sendResetLink(
            $request->only('correo')
        );

        if ($estado != Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'correo' => [__($estado)],
            ]);
        }

        return response()->json(['estado' => __($estado)]);
    }
}
