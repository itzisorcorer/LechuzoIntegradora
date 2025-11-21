<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class VendedorController extends Controller
{
    /**
     * Obtiene el perfil del vendedor autenticado via Token.
     */
    public function show(Request $request)
    {
        $user = Auth::user();
        if (!$user->vendedor) {
            return response()->json(['message' => 'No eres vendedor'], 404);
        }
        
        // Devolvemos el vendedor con su usuario (email)
        return response()->json($user->vendedor->load('user'), 200);
    }

    /**
     * Actualiza los datos de la tienda.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return response()->json(['message' => 'No eres vendedor'], 404);
        }

        // Validamos
        $request->validate([
            'nombre_tienda' => 'required|string|max:255',
            'description' => 'nullable|string',
            'foto' => 'nullable|image|max:5120', // Máximo 2MB
        ]);

        // Actualizamos
        $vendedor->update([
            'nombre_tienda' => $request->nombre_tienda,
            'description' => $request->description,
        ]);
        //para la foto
        if($request->hasFile('foto')){
            // Eliminar la foto anterior si existe
            $rutaVieja = $vendedor->getAttribute('url_foto') ?? null;
            if($rutaVieja){
                Storage::disk('public')->delete($rutaVieja);
            }
            // Guardar la nueva foto
            $path = $request->file('foto')->store('perfiles', 'public');
            $datosParaActualizar['url_foto'] = $path;
        }
        // Guardar los cambios
        $vendedor->update($datosParaActualizar);

        return response()->json([
            'message' => 'Perfil actualizado',
            'vendedor' => $vendedor
        ], 200);
    }
}
