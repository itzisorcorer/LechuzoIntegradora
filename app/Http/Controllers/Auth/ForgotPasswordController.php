<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

use App\Models\User;

class ForgotPasswordController extends Controller
{
    public function sendResetLinkEmail(Request $request)
    {
        // 1. Validar email
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'El correo no está registrado.', 'errors' => $validator->errors()], 422);
        }

        $email = $request->email;
        $codigo = rand(100000, 999999);

        // 2. Guardar Token en BD
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $codigo, 'created_at' => now()]
        );

        try {
            // 3. ENVIAR CORREO VÍA API HTTP (Resend)
            // Usamos la llave que ya pusiste en MAIL_PASSWORD para no crear variables nuevas
            $apiKey = env('MAIL_PASSWORD'); 

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json'
            ])->post('https://api.resend.com/emails', [
                'from' => 'onboarding@resend.dev',
                'to' => [$email],
                'subject' => 'Código de Recuperación - Lechuzo',
                'html' => "<p>Hola, tu código de recuperación es: <strong>$codigo</strong><br><br>Ingrésalo en la App.</p>"
            ]);

            // Verificar si Resend aceptó el correo
            if ($response->successful()) {
                return response()->json(['message' => '¡Código enviado! Revisa tu correo.'], 200);
            } else {
                // Si falla, logueamos qué dijo Resend
                Log::error('Error API Resend: ' . $response->body());
                return response()->json(['message' => 'Error al enviar el correo.'], 500);
            }

        } catch (\Exception $e) {
            Log::error('Error Exception Mail: ' . $e->getMessage());
            return response()->json(['message' => 'Error interno al procesar correo.'], 500);
        }
    }

    // ... (Mantén tu función resetPassword igual, esa no cambia) ...
    public function resetPassword(Request $request)
    {
        // (Pega aquí el mismo código de resetPassword que te di en la respuesta anterior)
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
            'codigo' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Datos inválidos', 'errors' => $validator->errors()], 422);
        }

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$registro || $registro->token != $request->codigo) {
            return response()->json(['message' => 'El código es incorrecto o ha expirado.'], 400);
        }

        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return response()->json(['message' => '¡Contraseña restablecida con éxito!'], 200);
    }
}