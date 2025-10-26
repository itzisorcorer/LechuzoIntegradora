<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Vendedor;
use App\Models\Estudiante;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario (Vendedor o Estudiante).
     */
    public function register(Request $request)
    {
        // 1. --- VALIDACIÓN ---
        // Validamos los datos de entrada
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', Rules\Password::defaults(), 'confirmed'], // 'confirmed' busca 'password_confirmation'
            'role' => ['required', 'string', 'in:vendedor,estudiante'], // Solo permitimos estos roles en el registro público

            // --- Campos de Perfil (Condicionales) ---
            'nombre_tienda' => ['required_if:role,vendedor', 'string', 'max:255'],
            'nombre_completo' => ['required_if:role,estudiante', 'string', 'max:255'],
            'matricula' => ['nullable', 'string', 'max:10', 'unique:estudiantes'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // 422 = Error de validación
        }

        // 2. --- TRANSACCIÓN DE BASE DE DATOS ---
        // Usamos una transacción para asegurarnos de que si algo falla, nada se guarde.
        // O se crea el User Y el Perfil, o no se crea NADA.
        try {
            DB::beginTransaction();

            // 3. --- CREAR EL USUARIO (TABLA 'USERS') ---
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            // 4. --- CREAR EL PERFIL (TABLA 'VENDEDORES' O 'ESTUDIANTES') ---
            if ($request->role === 'vendedor') {
                // Usamos la relación que definimos en el Modelo User
                $user->vendedor()->create([
                    'nombre_tienda' => $request->nombre_tienda,
                ]);
            } 
            elseif ($request->role === 'estudiante') {
                // Usamos la relación que definimos en el Modelo User
                $user->estudiante()->create([
                    'nombre_completo' => $request->nombre_completo,
                    'matricula' => $request->matricula,
                ]);
            }

            // Si todo salió bien, confirmamos los cambios en la BD
            DB::commit();

            // 5. --- CREAR TOKEN Y ENVIAR RESPUESTA ---
            // (Esto asume que tienes Laravel Sanctum instalado)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => '¡Usuario registrado exitosamente!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load(['vendedor', 'estudiante']) // Devolvemos el usuario con su perfil
            ], 201); // 201 = Creado

        } catch (\Exception $e) {
            // Si algo falló, revertimos la transacción
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error en el registro.',
                'error' => $e->getMessage() // En producción, quita getMessage()
            ], 500); // 500 = Error de servidor
        }
    }

    /**
     * Inicia sesión (Login).
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 1. --- INTENTAR AUTENTICAR ---
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Credenciales inválidas'], 401); // 401 = No autorizado
        }

        // 2. --- OBTENER USUARIO Y CREAR TOKEN ---
        $user = User::where('email', $request->email)->firstOrFail();

        // Opcional: Revocar tokens antiguos para que solo haya una sesión activa
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => '¡Login exitoso!',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load(['vendedor', 'estudiante']) // Devolvemos el usuario con su perfil
        ]);
    }

    /**
     * Cierra sesión (Logout).
     */
    public function logout(Request $request)
    {
        // Revoca el token que se usó para hacer la petición
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Sesión cerrada exitosamente']);
    }
}