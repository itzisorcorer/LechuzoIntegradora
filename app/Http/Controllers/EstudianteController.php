<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EstudianteController extends Controller
{
    /**
     * Obtener perfil
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user->estudiante) {
            return response()->json(['message' => 'No eres estudiante'], 404);
        }
        return response()->json($user->estudiante->load('user'), 200);
    }

    /**
     * Actualizar perfil (Nombre y Foto)
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $estudiante = $user->estudiante;

        if (!$estudiante) {
            return response()->json(['message' => 'No eres estudiante'], 404);
        }

        $request->validate([
            'nombre_completo' => 'required|string|max:255',
            'foto' => 'nullable|image|max:2048',
        ]);

        $datos = ['nombre_completo' => $request->nombre_completo];

        // Lógica de Foto
        if ($request->hasFile('foto')) {
            // Borrar vieja
            $rutaVieja = $estudiante->getAttributes()['url_foto'] ?? null;
            if ($rutaVieja) Storage::disk('public')->delete($rutaVieja);
            
            // Subir nueva
            $datos['url_foto'] = $request->file('foto')->store('perfiles_estudiantes', 'public');
        }

        $estudiante->update($datos);

        return response()->json([
            'message' => 'Perfil actualizado',
            'estudiante' => $estudiante
        ], 200);
    }
}
