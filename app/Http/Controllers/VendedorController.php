<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\Vendedor;

class VendedorController extends Controller
{
    /**
     * Muestra el perfil del vendedor autenticado.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'vendedor' && $user->role !== 'modulo') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $vendedor = $user->vendedor;
        
        // Incluimos el promedio de calificaciones si tienes esa relación, si no, quita el ->load()
        // $vendedor->load('calificaciones'); 

        return response()->json($vendedor, 200);
    }

    /**
     * Actualiza el perfil del vendedor.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        if ($user->role !== 'vendedor' && $user->role !== 'modulo') {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $vendedor = $user->vendedor;

        // Validación
        $validator = Validator::make($request->all(), [
            'nombre' => 'nullable|string|max:255', // En BD es 'nombre_tienda'
            'descripcion' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // --- CORRECCIÓN DEL ERROR ---
        // 1. Inicializamos el array de datos para actualizar
        $datosParaActualizar = [];

        // 2. Llenamos datos de texto si vienen en el request
        if ($request->has('nombre')) {
            $datosParaActualizar['nombre_tienda'] = $request->input('nombre');
        }
        if ($request->has('descripcion')) {
            $datosParaActualizar['description'] = $request->input('descripcion');
        }

        // 3. Manejo de la Imagen (Si viene una nueva)
        if ($request->hasFile('foto')) {
            // Borrar foto anterior si existe y no es una URL externa dummy
            if ($vendedor->url_foto && Storage::disk('public')->exists($vendedor->url_foto)) {
                Storage::disk('public')->delete($vendedor->url_foto);
            }

            // Guardar nueva
            $path = $request->file('foto')->store('vendedores', 'public');
            $datosParaActualizar['url_foto'] = $path;
        }

        // 4. Actualizar SOLO si hay algo que cambiar
        if (!empty($datosParaActualizar)) {
            $vendedor->update($datosParaActualizar);
        }

        return response()->json([
            'message' => 'Perfil actualizado correctamente',
            'vendedor' => $vendedor
        ], 200);
    }
}