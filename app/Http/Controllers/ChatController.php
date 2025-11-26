<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Chat;
use App\Models\Message;
use App\Models\Vendedor;
use App\Models\Estudiantes;

class ChatController extends Controller
{
        // 1. Iniciar o Buscar Chat (Para el botón "Contactar Vendedor")
    public function iniciarChat(Request $request)
    {
        $user = Auth::user();
        $vendedorId = $request->vendedor_id;
        $estudianteId = null;

        // Definir quién es quién
        if ($user->role === 'estudiante') {
            $estudianteId = $user->estudiante->id;
        } else {
            // Si un vendedor quiere iniciar chat (raro, pero posible), necesita el ID del estudiante
            $estudianteId = $request->estudiante_id;
            $vendedorId = $user->vendedor->id;
        }

        // Buscar si ya existe
        $chat = Chat::firstOrCreate([
            'estudiante_id' => $estudianteId,
            'vendedor_id' => $vendedorId
        ]);

        return response()->json($chat, 200);
    }

    // 2. Enviar Mensaje
    public function enviarMensaje(Request $request, $chatId)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $mensaje = Message::create([
            'chat_id' => $chatId,
            'sender_id' => Auth::id(), // El ID del usuario logueado (User table)
            'content' => $request->input('content'),
            'is_read' => false
        ]);

        return response()->json($mensaje, 201);
    }

    // 3. Ver Mensajes de un Chat (Historial)
    public function obtenerMensajes($chatId)
    {
        // Validar que el usuario pertenezca al chat por seguridad
        $user = Auth::user();
        $chat = Chat::findOrFail($chatId);

        // (Aquí podrías agregar validación: if user is not student or vendor of this chat -> 403)

        $mensajes = $chat->messages()
            ->with('sender') // Traer info de quien lo envió
            ->orderBy('created_at', 'asc') // Del más viejo al más nuevo
            ->get();

        return response()->json($mensajes, 200);
    }

    // 4. Lista de Chats (La bandeja de entrada)
    public function misChats()
    {
        $user = Auth::user();
        
        if ($user->role === 'estudiante') {
            $chats = Chat::where('estudiante_id', $user->estudiante->id)
                ->with('vendedor.user') // Para saber el nombre de la tienda
                ->orderBy('updated_at', 'desc')
                ->get();
        } else {
            $chats = Chat::where('vendedor_id', $user->vendedor->id)
                ->with('estudiante.user') // Para saber el nombre del alumno
                ->orderBy('updated_at', 'desc')
                ->get();
        }

        return response()->json($chats, 200);
    }
}
