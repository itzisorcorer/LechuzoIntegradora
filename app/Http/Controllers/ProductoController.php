<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Producto;
use App\Models\Categorias;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //verificar al usuario autenticado
        $user = Auth::user();

        //solo los vendedores pueden crear productos
        if ($user->role !== 'vendedor') {
            return response()->json([
                'message' => 'Solo los vendedores pueden crear productos.'
            ], 403);
        }

        //Validar los datos de entrada
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:50',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0.01',
            'categoria_id' => 'required|integer|exists:categorias,id',
            'cantidad_disponible' => 'nullable|integer|min:0',
            'url_imagen' => 'nullable|string|url' //validar que sea url
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        //OBTENER EL PERFIL DEL VENDEDOR:
        //Usamos la relación que definimos en el modelo User
        $vendedor = $user->vendedor;

        if (!$vendedor) {
            return response()->json([
                'message' => 'El usuario autenticado no tiene un perfil de vendedor.'
            ], 400);
        }

        //Crear el producto
        try{
            $producto = $vendedor->productos()->make([
                'nombre' => $request->nombre,
                'descripcion' => $request->descripcion,
                'precio' => $request->precio,
                'categoria_id' => $request->categoria_id,
                'cantidad_disponible' => $request->cantidad_disponible ?? 0, // Si es nulo, pone 0
                'url_imagen' => $request->url_imagen,
            ]);

            // El método save() NO revisa $fillable, simplemente guarda el modelo
            // tal como está (con el 'vendedor_id' que le puso la relación).
                $producto->save();
                return response()->json([
                'message' => 'Producto creado exitosamente',
                'producto' => $producto
            ], 201);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al crear el producto',
                'error' => $e->getMessage()
            ], 500);
        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
