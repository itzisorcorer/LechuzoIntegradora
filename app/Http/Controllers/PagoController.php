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
use MercadoPago\Client\Payment\PaymentClient;
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
            throw new Exception('El Access Token de Mercado Pago no está configurado en config/services.php');
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
        } catch (Exception $e) {
            return response()->json(['message' => 'Error al crear la preferencia de pago', 'error' => $e->getMessage()], 500);
        }
    }
public function recibirWebhook(Request $request)
    {
        // 1. Log para saber que al menos llegó la petición
        Log::info('Webhook recibido: ' . json_encode($request->all()));

        $topic = $request->input('topic') ?? $request->input('type');
        $id = $request->input('id') ?? $request->input('data.id');

        if ($topic !== 'payment' || empty($id)) {
            return response()->json(['status' => 'ignored'], 200);
        }

        try {
            // --- CORRECCIÓN DE SEGURIDAD ---
            // Aseguramos que el SDK tenga el token antes de usarlo
            $token = config('services.mercadopago.token');
            if(empty($token)) {
                throw new Exception('Token de MP no configurado en el servidor.');
            }
            MercadoPagoConfig::setAccessToken($token);
            // -------------------------------

            $client = new PaymentClient();
            $payment = $client->get($id);

            // Log para depurar qué responde MP
            Log::info('Pago consultado MP Estado: ' . $payment->status);

            $ordenId = $payment->external_reference;
            $status = $payment->status;

            if ($status === 'approved') {
                $orden = Ordenes::find($ordenId);
                if ($orden) {
                    // ✅ CORRECCIÓN: Usamos un valor válido del ENUM
                    $orden->status = 'confirmado'; 
                    $orden->save();
                    Log::info("Orden #$ordenId actualizada a CONFIRMADO (Pago Aprobado).");
                } else {
                    Log::warning("Orden #$ordenId no encontrada en BD.");
                }
            }

            return response()->json(['status' => 'success'], 200);

        } catch (MPApiException $e) {
            // IMPORTANTE: Devolvemos JSON para evitar que Laravel intente renderizar HTML y explote
            $response = $e->getApiResponse();
            $content = $response ? $response->getContent() : 'Sin contenido';
            Log::error('Error Webhook CRÍTICO: ' . json_encode($content));
            return response()->json(['error' => 'ERROR API MP', 'details' => $content], 500);
        }catch (Exception $e) {
            // IMPORTANTE: Devolvemos JSON para evitar que Laravel intente renderizar HTML y explote
            Log::error('Error Webhook genérico: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}