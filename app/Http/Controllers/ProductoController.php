<?php

namespace App\Http\Controllers;

use App\Models\Productos;
use Exception;
use Illuminate\Http\Request;

use App\Models\Producto;
use App\Models\Categorias;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ProductoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //  'paginate()' sirve para no traer miles de registros a la vez.
        // Usamos 'with()' para cargar las relaciones del vendedor y la categoría.
        // Esto se llama "Eager Loading" (Carga Ansiosa).
        try{
            $productos = Productos::with(['vendedor.user', 'categoria'])->paginate(10);
            return response()->json($productos, 200);
        }catch(\Exception $e){
            return response()->json([
                'message' => 'Error al obtener los productos',
                'error' => $e->getMessage()
            ], 500);
        }
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
                'producto' => $producto->load(['vendedor', 'categoria'])
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
        //findOrFaill sirve para qque falle automaticamente si no encuentra el id
        try{
            $producto = Productos::with(['vendedor', 'categoria'])->findOrFail($id);
        
        
        //devolver producto encontrado
        return response()->json([$producto], 200);



    }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
        return response()->json([
            'message' => 'Producto no encontrado',
            'error' => $e->getMessage()
        ], 404);
    }catch(\Exception $e){
        return response()->json([
            'message' => 'Error al obtener el producto',
            'error' => $e->getMessage()
        ], 500);
    }

}

    

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        // Las reglas son similares a 'store', pero 'required' cambia a 'sometimes'.
        // 'sometimes' significa: "si el campo está presente, valídalo".
        $validator = Validator::make($request->all(), [
            'nombre' => 'sometimes|required|string|max:50',
            'descripcion' => 'sometimes|nullable|string',
            'precio' => 'sometimes|required|numeric|min:0.01',
            'categoria_id' => 'sometimes|required|integer|exists:categorias,id',
            'cantidad_disponible' => 'sometimes|nullable|integer|min:0',
            'url_imagen' => 'sometimes|nullable|string|url',
            'disponible' => 'sometimes|nullable|boolean'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }
        try{
            //buscamos el producto
            $producto = Productos::findOrFail($id);
            if($user->role !== 'vendedor' || !$user->vendedor || $producto->vendedor_id !== $user->vendedor->id){
                return response()->json([
                    'message' => 'No autorizado. No eres el propietario de este producto.'
                ], 403);

            }
            //actualizamos los campos si están presentes en la solicitud
            $producto->update($request->all());

            return response()->json([
                'message' => 'Producto actualizado exitosamente.',
                'producto' => $producto->load(['vendedor', 'categoria'])
            ], 200);
        }catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Producto no encontrado.'], 404);



    }catch (Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el producto.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        $user = Auth::user();

        try{
            $producto = Productos::findOrFail($id);

            //verificamos que el usuario sea el vendedor y que el id 
            //del vendedor coincida con el del producto

            if($user->role !== 'vendedor' || !$user->vendedor || $producto->vendedor_id !== $user->vendedor->id){
                return response()->json([
                    'message' => 'No autorizado. No eres el propietario de este producto.'
                ], 403);
            }
            $producto->delete();
            return response()->json([
                'message' => 'Producto eliminado exitosamente.'
            ], 200);
    }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $e){
        //esta es la parte que se activa por FindOrFail
        return response()->json([
            'message' => 'Producto no encontrado.',
        ], 404);
    }catch(Exception $e){
        return response()->json([
            'message' => 'Error al eliminar el producto.',
            'error' => $e->getMessage()
        ], 500);

        }
    }
    //Esta función se encarga de devolver todos los productos del vendedor autenticado
    public function misProductos(Request $request){
        $user = $request->user();

        if($user->role !== 'vendedor' && $user->role !== 'modulo'){
            return response()->json(['message' => 'Acción no autorizada'], 403);
        }
        //obtener el perfil del vendedor
        $vendedor = $user->vendedor;
        if(!$vendedor){
            return response()->json(['message' => 'Perfil no encontrado'], 404);

        }
        //a traves de las relaciones de los modelos, traemos los productos del vendedor 
        $productos = $vendedor->productos()->with(['categoria', 'vendedor'])->orderBy('created_at', 'desc')->paginate(15);
        return response()->json($productos);


    }
}
