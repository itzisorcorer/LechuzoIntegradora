<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

class ForgotPasswordController extends Controller
{
    /**
     * Recibe el email y envía el link de reseteo.
     */
    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validar el email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Email inválido', 'errors' => $validator->errors()], 422);
        }

        // 2. Enviar el link (Laravel hace la magia con Gmail)
        // Esto busca el usuario, genera un token seguro y le manda el correo
        $response = Password::sendResetLink($request->only('email'));

        // 3. Responder a la App
        if ($response == Password::RESET_LINK_SENT) {
            return response()->json(['message' => '¡Correo enviado! Revisa tu bandeja.'], 200);
        } else {
            return response()->json(['message' => 'No se pudo enviar el correo. Verifica que esté registrado.'], 400);
        }
    }
}