<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Ordenes;
use App\Models\Productos;

use App\Models\Items_Ordenes;

class OrdenController extends Controller
{
    //Almacena una nueva orden (Checkout)
    //Recibe un carrito y lo divide por ordenes del vendedor
    public function store(Request $request)
    {
        // 1. --- Autenticación y Autorización ---
        // Obtenemos el usuario autenticado
        $user = Auth::user();

        // Solo los 'estudiantes' pueden comprar
        if ($user->role !== 'estudiante') {
            return response()->json(['message' => 'Solo los estudiantes pueden realizar compras.'], 403); // Prohibido
        }
        
        // Obtenemos el perfil de estudiante (necesitamos su 'id')
        $estudiante = $user->estudiante;
        if (!$estudiante) {
            return response()->json(['message' => 'Perfil de estudiante no encontrado.'], 404);
        }

        // 2. --- Validación del Carrito ---
        // Esperamos que Flutter envíe: { "items": [ { "producto_id": 1, "cantidad": 2 }, ... ] }
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|integer|exists:productos,id',
            'items.*.cantidad' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422); // Error de validación
        }

        $itemsDelCarrito = $request->items;

        // --- 3. Lógica de Negocio (El "Cerebro") ---
        try {
            // Iniciamos una transacción. Si algo falla, se deshace TODO.
            DB::beginTransaction();

            // Obtenemos los IDs de los productos
            $productoIds = collect($itemsDelCarrito)->pluck('producto_id');

            // --- ¡IMPORTANTE! ---
            // Traemos los productos de la BD para usar NUESTRO precio (seguro)
            // y saber a qué vendedor pertenecen.
            $productosEnDB = Productos::find($productoIds);
            
            // Creamos un "mapa" de las cantidades que envió el cliente
            // (ej. [ 'producto_id_1' => 2 (cantidad), 'producto_id_5' => 1 (cantidad) ])
            $itemsMap = collect($itemsDelCarrito)->keyBy('producto_id')->map(fn($item) => $item['cantidad']);

            // --- ¡LA MAGIA! Agrupamos los productos por vendedor_id ---
            $itemsAgrupadosPorVendedor = $productosEnDB->groupBy('vendedor_id');

            $ordenesCreadasIds = [];

            // 4. --- Creación de Órdenes (Una por Vendedor) ---
            
            // Iteramos sobre cada grupo (ej. 2 productos para Vendedor A, 1 para Vendedor B)
            foreach ($itemsAgrupadosPorVendedor as $vendedorId => $productosDelVendedor) {
                
                // a) Calculamos el total de ESTA orden
                $cantidadTotalOrden = 0;
                foreach ($productosDelVendedor as $producto) {
                    $cantidad = $itemsMap[$producto->id];
                    // (Precio de la BD * Cantidad del cliente)
                    $cantidadTotalOrden += $producto->precio * $cantidad; 
                }

                // b) Creamos la cabecera de la Orden
                $orden = Ordenes::create([
                    'estudiante_id' => $estudiante->id,
                    'vendedor_id' => $vendedorId,
                    'status' => 'pendiente', // Estado inicial
                    'cantidad_total' => $cantidadTotalOrden,
                ]);
                //guardamos el id de la orden creada
                $ordenesCreadasIds[] = $orden->id;

                // c) Preparamos los "items" para esta orden
                $itemsParaInsertar = [];
                foreach ($productosDelVendedor as $producto) {
                    $itemsParaInsertar[] = [
                        'orden_id' => $orden->id,
                        'producto_id' => $producto->id,
                        'cantidad' => $itemsMap[$producto->id],
                        'precio_de_compra' => $producto->precio, // Guardamos el precio del momento
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                // d) Insertamos todos los items en la BD
                Items_Ordenes::insert($itemsParaInsertar);
            }

            // Si todo salió bien en el loop, confirmamos los cambios
            DB::commit();

            //devolvemos los ids de las ordenes creadas

            return response()->json([
                'message' => '¡Pedido(s) creado(s) exitosamente!',
                'orden_ids' => $ordenesCreadasIds
            ], 201); // 201 = Creado

        } catch (\Exception $e) {
            // Si algo falló (ej. un producto sin stock), revertimos TODO
            DB::rollBack();
            return response()->json([
                'message' => 'Error al procesar el pedido.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    //Muestra el historial de ordenes del estudiaante autenticado
    public function misOrdenes(Request $request)
    {
        $user = Auth::user();

        // Solo los 'estudiantes' pueden ver sus órdenes
        if ($user->role !== 'estudiante') {
            return response()->json(['message' => 'Acción no autorizada.'], 403);
        }
        $estudiante = $user->estudiante;
        if (!$estudiante) {
            return response()->json(['message' => 'Perfil de estudiante no encontrado.'], 404);
        }
        
        try {
            //consultamos las ordenes del estudiante autenticado
            $ordenes = Ordenes::where('estudiante_id', $estudiante->id)
            


                ->whereHas('itemsOrdenes') // Solo órdenes con items
                ->whereHas('itemsOrdenes.producto')
                ->whereHas('vendedor')
                ->with([
                    'vendedor', // ¿A quién le compré?
                    'itemsOrdenes.producto.vendedor', // ¿Qué productos compré?
                    'itemsOrdenes.producto.categoria', // ¿Qué productos compré?
                ])
                
                ->orderBy('created_at', 'desc') 
                ->paginate(10);

            return response()->json($ordenes, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las órdenes.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Muestra las ventas recibidas por el vendedor autenticado.
     * (GET /api/vendedor/ordenes)
     */
    public function misVentas(Request $request)
    {
        $user = Auth::user();
        if($user->role !== 'vendedor' && $user->role !== 'modulo'){
            return response()->json(['message' => 'Acción no autorizada.'], 403);
        }

        $vendedor = $user->vendedor;
        if(!$vendedor){
            return response()->json(['message' => 'Perfil de vendedor no encontrado.'], 404);
        }
        try {
            //consulta
            $ventas = Ordenes::where('vendedor_id', $vendedor->id)
            ->whereHas('itemsOrdenes.producto')
            ->whereHas('vendedor')
            ->with([
                'vendedor', // vendedor de la orden
                'itemsOrdenes.producto.vendedor', // vendedor del producto
                'itemsOrdenes.producto.categoria', // categoria del producto
            ]) ->orderBy('created_at', 'desc')->paginate(10);
            return response()->json($ventas, 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener las ventas.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
