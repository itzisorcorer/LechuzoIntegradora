<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
        ]);

        // Actualizamos
        $vendedor->update([
            'nombre_tienda' => $request->nombre_tienda,
            'description' => $request->description,
        ]);

        return response()->json([
            'message' => 'Perfil actualizado',
            'vendedor' => $vendedor
        ], 200);
    }
}
