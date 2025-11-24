<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use MercadoPago\SDK;
use MercadoPago\Resources\Preference;
use MercadoPago\Resources\Item;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Client\Preference\PreferenceClient; 
use MercadoPago\Exceptions\MPApiException; 
use MercadoPago\Client\Payments\PaymentsClient;
use Illuminate\Support\Facades\Log;

use App\Models\Ordenes;
use App\Models\Pagos;
use Exception;

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
                    "success" => "lechuzopay://callback?status=success",
                    "failure" => "lechuzopay://callback?status=failure",
                    "pending" => "lechuzopay://callback?status=pending"
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
    public function recibirWebhook(Request $request){
        //validar que ses evento de pago
        //SE VALIDA TYPE Y TOPIC, ya que MP usa los dos depende de la versión
        $topic = $request->input('topic') ?? $request->input('type');
        $id = $request->input('id') ?? $request->input('data.id');

        if($topic !== 'payment' || empty($id)){
            //si no es un evento de pago, responder con 200 para que MP no insista
            return response()->json(['status' => 'ignored'], 200);
        }
        try {
            // consultar el status real de MP, es necesario verificar el ID
            $client = new PaymentsClient();
            $payment = $client->get($id);

            //obtener id
            $ordenId = $payment->external_reference;
            $status = $payment->status;

            //actualizar la orden local
            $orden = Ordenes::find($ordenId);
            if($orden){
                if($status === 'approved'){
                    $orden->status = 'pagado';
                    $orden->save();

                    //actualizar el pago en tabla Pagos
                    $pago = Pagos::where('orden_id', $ordenId)->first();
                    if($pago){ $pago->update(['status' => 'pagado', 'id_transaccion' => $id]); }
            }
            else if ($status === 'rejected' || $status === 'cancelled'){
                $orden->status = 'cancelado';
                $orden->save();

                //actualizar el pago en tabla Pagos
                $pago = Pagos::where('orden_id', $ordenId)->first();
                if($pago){ $pago->update(['status' => 'cancelado', 'id_transaccion' => $id]); }

            }
            return response()->json(['status' => 'success'], 200);
            }
        } catch (Exception $e) {
            //error para poder verlo en railway si falla
            Log::error('Error al procesar webhook de Mercado Pago: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

    }

}