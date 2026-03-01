<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    /**
     * Constructor - aplicar middleware de autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar lista de chats
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener todas las conversaciones del usuario
        $conversations = Conversation::where('user_one', $user->id)
                        ->orWhere('user_two', $user->id)
                        ->with(['userOne', 'userTwo', 'lastMessage'])
                        ->orderBy('updated_at', 'desc')
                        ->get();
        
        // Obtener contactos (usuarios con los que ha hablado)
        $contacts = collect();
        foreach($conversations as $conv) {
            $otherUser = $conv->user_one == $user->id ? $conv->userTwo : $conv->userOne;
            if ($otherUser) {
                $contacts->push($otherUser);
            }
        }
        
        return view('chats.index', compact('conversations', 'contacts'));
    }

    /**
     * Mostrar una conversación específica
     */
    public function show($userId)
    {
        $currentUser = Auth::user();
        $otherUser = User::findOrFail($userId);
        
        // Buscar o crear conversación
        $conversation = Conversation::where(function($query) use ($currentUser, $otherUser) {
            $query->where('user_one', $currentUser->id)
                  ->where('user_two', $otherUser->id);
        })->orWhere(function($query) use ($currentUser, $otherUser) {
            $query->where('user_one', $otherUser->id)
                  ->where('user_two', $currentUser->id);
        })->first();
        
        // Si no existe, crear nueva conversación
        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one' => $currentUser->id,
                'user_two' => $otherUser->id
            ]);
        }
        
        // Marcar mensajes como leídos
        Message::where('conversation_id', $conversation->id)
              ->where('user_id', $otherUser->id)
              ->where('is_read', false)
              ->update(['is_read' => true]);
        
        // Obtener mensajes
        $messages = Message::where('conversation_id', $conversation->id)
                          ->orderBy('created_at', 'asc')
                          ->get();
        
        // Obtener todas las conversaciones para el sidebar
        $conversations = Conversation::where('user_one', $currentUser->id)
                        ->orWhere('user_two', $currentUser->id)
                        ->with(['userOne', 'userTwo', 'lastMessage'])
                        ->orderBy('updated_at', 'desc')
                        ->get();
        
        return view('chats.show', compact('conversation', 'otherUser', 'messages', 'conversations'));
    }

    /**
     * Enviar mensaje de texto
     */
    public function sendMessage(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'message' => 'required|string|max:5000'
            ]);

            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'type' => 'text',
                'is_read' => false
            ]);

            // Actualizar timestamp de la conversación
            $conversation = Conversation::find($request->conversation_id);
            $conversation->touch();

            // Cargar relación del usuario para la respuesta
            $message->load('user');

            return response()->json([
                'success' => true,
                'message' => 'Mensaje enviado',
                'data' => $message,
                'user' => Auth::user()->only(['id', 'name', 'avatar'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando mensaje: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el mensaje: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Enviar archivo (imagen/video)
     */
    public function sendFile(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id',
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480' // 20MB max
            ]);

            $file = $request->file('file');
            
            // Determinar tipo
            $mime = $file->getMimeType();
            $type = strpos($mime, 'image/') === 0 ? 'image' : 'video';
            
            // Crear carpeta según tipo y fecha
            $folder = $type == 'image' ? 'images' : 'videos';
            $path = $file->store("chat-files/{$folder}/" . date('Y/m'), 'public');
            
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'user_id' => Auth::id(),
                'type' => $type,
                'file_path' => $path,
                'message' => null,
                'is_read' => false
            ]);

            // Actualizar timestamp de la conversación
            $conversation = Conversation::find($request->conversation_id);
            $conversation->touch();

            $message->load('user');

            // Obtener URL completa
            $fileUrl = Storage::url($path);
            
            Log::info('Archivo guardado en: ' . $path);
            Log::info('URL generada: ' . $fileUrl);

            return response()->json([
                'success' => true,
                'message' => 'Archivo enviado',
                'data' => $message,
                'file_url' => $fileUrl,
                'user' => Auth::user()->only(['id', 'name', 'avatar'])
            ]);

        } catch (\Exception $e) {
            Log::error('Error enviando archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al enviar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Buscar usuarios
     */
    public function searchUsers(Request $request)
    {
        try {
            $search = $request->get('q');
            
            Log::info('Buscando usuarios: ' . $search);
            
            $users = User::where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%")
                         ->where('id', '!=', Auth::id())
                         ->limit(10)
                         ->get(['id', 'name', 'email', 'avatar', 'is_online']);
            
            Log::info('Usuarios encontrados: ' . $users->count());
            
            return response()->json($users);
            
        } catch (\Exception $e) {
            Log::error('Error en búsqueda: ' . $e->getMessage());
            return response()->json(['error' => 'Error en búsqueda'], 500);
        }
    }

    /**
     * Obtener mensajes no leídos
     */
    public function getUnreadCount()
    {
        $user = Auth::user();
        
        $conversations = Conversation::where('user_one', $user->id)
                        ->orWhere('user_two', $user->id)
                        ->get();
        
        $totalUnread = 0;
        
        foreach($conversations as $conv) {
            $otherUserId = $conv->user_one == $user->id ? $conv->user_two : $conv->user_one;
            
            $unread = Message::where('conversation_id', $conv->id)
                           ->where('user_id', $otherUserId)
                           ->where('is_read', false)
                           ->count();
            
            $totalUnread += $unread;
        }
        
        return response()->json(['unread' => $totalUnread]);
    }

    /**
     * Marcar mensajes como leídos
     */
    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);

            Message::where('conversation_id', $request->conversation_id)
                  ->where('user_id', '!=', Auth::id())
                  ->where('is_read', false)
                  ->update(['is_read' => true]);

            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            Log::error('Error marcando mensajes como leídos: ' . $e->getMessage());
            return response()->json(['error' => 'Error'], 500);
        }
    }

    /**
     * Obtener mensajes nuevos desde un ID
     */
    public function getNewMessages($conversationId, $lastMessageId)
    {
        try {
            $messages = Message::where('conversation_id', $conversationId)
                        ->where('id', '>', $lastMessageId)
                        ->with('user')
                        ->orderBy('created_at', 'asc')
                        ->get();
            
            return response()->json($messages);
            
        } catch (\Exception $e) {
            Log::error('Error obteniendo mensajes nuevos: ' . $e->getMessage());
            return response()->json(['error' => 'Error'], 500);
        }
    }
}