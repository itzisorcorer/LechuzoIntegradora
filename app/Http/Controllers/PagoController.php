<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MercadoPago\SDK;
use MercadoPago\Resources\Preference;
use MercadoPago\Resources\Item;
use MercadoPago\MercadoPagoConfig; // <-- ¡Asegúrate de importar este!
use MercadoPago\Client\Preference\PreferenceClient; // <-- ¡Y este!
use MercadoPago\Exceptions\MPApiException; // <-- ¡Y este!

use App\Models\Ordenes;
use App\Models\Pagos;

class PagoController extends Controller
{
    public function __construct()
    {
        // --- ¡AQUÍ ESTÁ LA CORRECCIÓN 1! ---
        // Leemos la llave desde 'config/services.php', que SÍ está en la caché
        $accessToken = config('services.mercadopago.token');

        if (empty($accessToken)) {
            // Si esto falla, ¡es porque no guardaste el config/services.php!
            throw new \Exception('El Access Token de Mercado Pago no está configurado en config/services.php');
        }

        // Usamos la inicialización V3 (la que ya tenías)
        MercadoPagoConfig::setAccessToken($accessToken);
    }
    
    //Crear una preferencia de pago de MP para una orden específica
    public function crearPreferenciaDePago(Request $request, $id)
    {
        $user = Auth::user();

        try {
            $orden = Ordenes::findOrFail($id);

            if($user->estudiante->id !== $orden->estudiante_id){
                return response()->json(['message' => 'No autorizado para pagar esta orden'], 403);
            }
            if($orden->status !== 'pendiente'){
                return response()->json(['message' => 'La orden ya ha sido pagada o cancelada'], 422);
            }
            
            // --- (Usamos la lógica V3 que ya tenías) ---
            $item = [
                "title" => 'Pedido #' . $orden->id . ' - ' . $orden->vendedor->nombre_tienda,
                "quantity" => 1,
                "unit_price" => (float) $orden->cantidad_total,
                "currency_id" => "MXN",
            ];
            $preferenceRequest = [
                "items" => [$item],
                "back_urls" => [
                    "success" => "https.tu-app.com/pago-exitoso",
                    "failure" => "https.tu-app.com/pago-fallido",
                    "pending" => "https.tu-app.com/pago-pendiente"
                ],
                "auto_return" => "approved",
                "external_reference" => $orden->id,
            ];
            $client = new PreferenceClient();
            $preference = $client->create($preferenceRequest);
            // --- (Fin de la lógica V3) ---

            $pago = Pagos::create([
                'orden_id' => $orden->id,
                'cantidad' => $orden->cantidad_total,
                'metodo' => 'MercadoPago',
                'status' => 'pendiente', 
                'id_transaccion' => $preference->id,
            ]);

            // --- ¡AQUÍ ESTÁ LA CORRECCIÓN 2! ---
            // Devolvemos el init_point (para Custom Tabs)
            // Y la public_key (para el SDK nativo, ¡por si acaso!)
            return response()->json([
                'init_point' => $preference->init_point,
                'public_key' => config('services.mercadopago.public_key') // <-- ¡Leemos la public key!
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        } catch (MPApiException $e) { 
            return response()->json(['message' => 'Error de Mercado Pago', 'error' => $e->getApiResponse()->getContent()], 500);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error al crear la preferencia de pago', 'error' => $e->getMessage()], 500);
        }
    }
}