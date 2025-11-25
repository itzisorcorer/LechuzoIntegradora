<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class ForgotPasswordController extends Controller
{
    // 1. Enviar el Código
    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'El correo no está registrado.', 'errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        // Generamos un código simple de 6 dígitos
        $codigo = rand(100000, 999999);

        // Guardamos/Actualizamos en la tabla password_reset_tokens
        // Nota: Guardamos el código tal cual (sin Hash) para validación simple numérica
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $codigo, 'created_at' => now()]
        );

        try {
            // ✅ EL TRUCO: Usamos Mail::raw para enviar texto plano. Cero Vite, Cero errores.
            Mail::raw("Hola, tu código de recuperación para Lechuzo es: $codigo\n\nIngrésalo en la App para restablecer tu contraseña.", function ($message) use ($email) {
                $message->to($email)
                        ->subject('Código de Recuperación - Lechuzo');
            });

            return response()->json(['message' => '¡Código enviado! Revisa tu correo.'], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al enviar correo: ' . $e->getMessage()], 500);
        }
    }

    // 2. Validar Código y Cambiar Contraseña (Paso 2)
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'codigo' => 'required', // El token numérico
            'password' => 'required|min:6|confirmed', // password y password_confirmation
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        // Verificamos si el código es correcto
        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$registro || $registro->token != $request->codigo) {
            return response()->json(['message' => 'El código es incorrecto o ha expirado.'], 400);
        }

        // ¡Todo bien! Cambiamos la contraseña
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Borramos el código usado
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => '¡Contraseña restablecida con éxito! Ya puedes iniciar sesión.'], 200);
    }
}