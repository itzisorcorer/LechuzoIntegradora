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
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

use App\Rules\ValidaMatriculaPorPrograma;

class AuthController extends Controller
{
    /**
     * Registra un nuevo usuario (Vendedor o Estudiante).
     */
    public function register(Request $request)
    {
        // --- INICIA LA CORRECCIÓN ---

        // 1. Definimos las reglas base para 'matricula'
        $matriculaRules = [
            'required',
            'string',
            'digits:10',
            // 2. Ponemos tu regla personalizada (que depende de 'programa_educativo_id')
            new ValidaMatriculaPorPrograma,
        ];

        // 3. Obtenemos el rol del request
        $role = $request->input('role');

        // 4. Añadimos la regla 'unique' correcta dinámicamente
        if ($role === 'estudiante') {
            // Si es estudiante, debe ser única en la tabla 'estudiantes'
            $matriculaRules[] = Rule::unique('estudiantes', 'matricula');
        
        } elseif ($role === 'vendedor' || $role === 'modulo') {
            // Si es vendedor o modulo, debe ser única en 'vendedores'
            $matriculaRules[] = Rule::unique('vendedores', 'matricula');
        }

        // 5. Ahora sí, creamos el validador con la lista de reglas completa
        $validator = Validator::make($request->all(), [
            'role' => ['required', 'string', 'in:admin,modulo,vendedor,estudiante'],
            'email' => ['required', 'string', 'email', 'max:80', 'unique:users'],
            'password' => ['required', 'string', Rules\Password::defaults(), 'confirmed'],
            
            // Ponemos la validación de programa PRIMERO
            'programa_educativo_id' => ['required', 'integer', 'exists:programas_educativos,id'],
            
            // Pasamos nuestro array de reglas dinámicas
            'matricula' => $matriculaRules,

            // --- FIN DE LA CORRECCIÓN ---

            'nombre_tienda' => ['required_if:role,vendedor', 'required_if:role,modulo', 'string', 'max:255'],
            'nombre_completo' => ['required_if:role,estudiante', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // 2. --- TRANSACCIÓN DE BASE DE DATOS ---
        // (El resto de tu función de crear el usuario está perfecta)
        try {
            DB::beginTransaction();

            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
            ]);

            if ($request->role === 'vendedor' || $request->role === 'modulo') {
                $user->vendedor()->create([
                    'nombre_tienda' => $request->nombre_tienda,
                    'matricula' => $request->matricula,
                    'programa_educativo_id' => $request->programa_educativo_id,
                ]);
            } 
            elseif ($request->role === 'estudiante') {
                $user->estudiante()->create([
                    'nombre_completo' => $request->nombre_completo,
                    'matricula' => $request->matricula,
                    'programa_educativo_id' => $request->programa_educativo_id,
                ]);
            }

            DB::commit();

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => '¡Usuario registrado exitosamente!',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user->load(['vendedor', 'estudiante']) 
            ], 201); 

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Hubo un error en el registro.',
                'error' => $e->getMessage()
            ], 500);
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