<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

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

        $conversations = Conversation::where('user_one', $user->id)
            ->orWhere('user_two', $user->id)
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();

        $contacts = collect();
        foreach ($conversations as $conv) {
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

        $conversation = Conversation::where(function ($query) use ($currentUser, $otherUser) {
            $query->where('user_one', $currentUser->id)
                ->where('user_two', $otherUser->id);
        })->orWhere(function ($query) use ($currentUser, $otherUser) {
            $query->where('user_one', $otherUser->id)
                ->where('user_two', $currentUser->id);
        })->first();

        if (!$conversation) {
            $conversation = Conversation::create([
                'user_one' => $currentUser->id,
                'user_two' => $otherUser->id
            ]);
        }

        Message::where('conversation_id', $conversation->id)
            ->where('user_id', $otherUser->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::where('conversation_id', $conversation->id)
            ->orderBy('created_at', 'asc')
            ->get();

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

            $conversation = Conversation::findOrFail($request->conversation_id);

            if (!$this->userBelongsToConversation(Auth::id(), $conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar mensajes en esta conversación.'
                ], 403);
            }

            // Guardar mensaje del usuario
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'user_id' => Auth::id(),
                'message' => trim($request->message),
                'type' => 'text',
                'is_read' => false
            ]);

            $conversation->touch();
            $message->load('user');

            // Si NO es conversación con IA, responder normal
            if (!$this->isAiConversation($conversation)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado',
                    'data' => $message,
                    'user' => Auth::user()->only(['id', 'name', 'avatar'])
                ]);
            }

            // Si SÍ es conversación con IA, pedir respuesta a Gemini
            try {
                $aiText = $this->generateAiReply($conversation);

                $aiMessage = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $this->getAiUserIdFromConversation($conversation),
                    'message' => $aiText,
                    'type' => 'text',
                    'is_read' => true
                ]);

                $conversation->touch();
                $aiMessage->load('user');

                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado',
                    'data' => $message,
                    'ai_message' => $aiMessage,
                    'user' => Auth::user()->only(['id', 'name', 'avatar'])
                ]);
            } catch (\Throwable $aiError) {
                Log::error('Error respondiendo con IA: ' . $aiError->getMessage(), [
                    'conversation_id' => $conversation->id
                ]);

                // El mensaje del usuario ya quedó guardado.
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado, pero la IA no pudo responder en este momento.',
                    'data' => $message,
                    'ai_error' => $aiError->getMessage(),
                    'user' => Auth::user()->only(['id', 'name', 'avatar'])
                ]);
            }

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
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480'
            ]);

            $conversation = Conversation::findOrFail($request->conversation_id);

            if (!$this->userBelongsToConversation(Auth::id(), $conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para enviar archivos en esta conversación.'
                ], 403);
            }

            $file = $request->file('file');

            $mime = $file->getMimeType();
            $type = strpos($mime, 'image/') === 0 ? 'image' : 'video';

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

            $conversation->touch();
            $message->load('user');

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

            $users = User::where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
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

        foreach ($conversations as $conv) {
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

    /**
     * Verifica si el usuario pertenece a la conversación
     */
    private function userBelongsToConversation(int $userId, Conversation $conversation): bool
    {
        return (int) $conversation->user_one === $userId || (int) $conversation->user_two === $userId;
    }

    /**
     * Verifica si la conversación es con la IA
     */
    private function isAiConversation(Conversation $conversation): bool
    {
        $aiBotUserId = (int) config('services.gemini.bot_user_id');

        if (!$aiBotUserId) {
            return false;
        }

        return (int) $conversation->user_one === $aiBotUserId
            || (int) $conversation->user_two === $aiBotUserId;
    }

    /**
     * Obtiene el ID del bot IA dentro de la conversación
     */
    private function getAiUserIdFromConversation(Conversation $conversation): ?int
    {
        $aiBotUserId = (int) config('services.gemini.bot_user_id');

        if (
            (int) $conversation->user_one === $aiBotUserId ||
            (int) $conversation->user_two === $aiBotUserId
        ) {
            return $aiBotUserId;
        }

        return null;
    }

    /**
     * Generar respuesta de IA usando Gemini
     */
    private function generateAiReply(Conversation $conversation): string
    {
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-2.5-flash');
        $aiUserId = $this->getAiUserIdFromConversation($conversation);

        if (!$apiKey) {
            throw new \Exception('No existe GEMINI_API_KEY en el archivo .env');
        }

        if (!$aiUserId) {
            throw new \Exception('No se pudo identificar el usuario bot');
        }

        $history = Message::where('conversation_id', $conversation->id)
            ->orderBy('id', 'desc')
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        $contents = [];

        foreach ($history as $msg) {
            $text = $this->normalizeMessageForGemini($msg);

            if (!$text) {
                continue;
            }

            $contents[] = [
                'role' => ((int) $msg->user_id === (int) $aiUserId) ? 'model' : 'user',
                'parts' => [
                    ['text' => $text]
                ]
            ];
        }

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => 0.8,
                'maxOutputTokens' => 1500,
            ]
        ];

        $response = \Illuminate\Support\Facades\Http::timeout(60)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                $payload
            );

        if (!$response->successful()) {
            \Illuminate\Support\Facades\Log::error('Gemini HTTP error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception("Gemini devolvió HTTP {$response->status()}");
        }

        $json = $response->json();

        \Illuminate\Support\Facades\Log::info('Gemini raw response', $json);

        $text = '';

        $candidates = data_get($json, 'candidates', []);
        foreach ($candidates as $candidate) {
            $parts = data_get($candidate, 'content.parts', []);
            foreach ($parts as $part) {
                if (!empty($part['text'])) {
                    $text .= $part['text'];
                }
            }
        }

        $text = trim($text);

        if ($text === '') {
            $blockReason = data_get($json, 'promptFeedback.blockReason');
            if ($blockReason) {
                throw new \Exception("La solicitud fue bloqueada: {$blockReason}");
            }

            throw new \Exception('Gemini no devolvió texto en la respuesta');
        }

        Log::info('Texto IA final', [
            'length' => mb_strlen($text),
            'text' => $text
        ]);

        return $text;
    }
    /**
     * Convierte mensajes de tu BD al formato de texto que entiende Gemini
     */
    private function normalizeMessageForGemini(Message $message): ?string
    {
        if ($message->type === 'text') {
            return trim((string) $message->message);
        }

        if ($message->type === 'image') {
            return '[El usuario envió una imagen. Esta integración básica no reenvía el binario al modelo.]';
        }

        if ($message->type === 'video') {
            return '[El usuario envió un video. Esta integración básica no reenvía el binario al modelo.]';
        }

        return null;
    }
}