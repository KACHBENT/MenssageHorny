<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Message;
use App\Models\Conversation;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ChatController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();

        $conversations = $this->getSidebarConversations($user->id);
        $groups = $this->getSidebarGroups($user->id);

        $contacts = collect();
        foreach ($conversations as $conv) {
            $otherUser = (int) $conv->user_one === (int) $user->id ? $conv->userTwo : $conv->userOne;
            if ($otherUser) {
                $contacts->push($otherUser);
            }
        }

        return view('chats.index', compact('conversations', 'groups', 'contacts'));
    }

    public function show($userId)
    {
        $currentUser = Auth::user();

        if ((int) $currentUser->id === (int) $userId) {
            return redirect()
                ->route('chats.index')
                ->with('error', 'No puedes abrir un chat contigo mismo.');
        }

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
            ->with('user')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($message) => $this->formatMessage($message));

        $conversations = $this->getSidebarConversations($currentUser->id);
        $groups = $this->getSidebarGroups($currentUser->id);

        return view('chats.show', compact(
            'conversation',
            'otherUser',
            'messages',
            'conversations',
            'groups'
        ));
    }

    public function sendMessage(Request $request)
    {
        try {
            $request->merge([
                'message' => trim((string) $request->message)
            ]);

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

            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'user_id' => Auth::id(),
                'message' => $request->message,
                'type' => 'text',
                'is_read' => false
            ]);

            $conversation->touch();
            $message->load('user');

            if (!$this->isAiConversation($conversation)) {
                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado',
                    'data' => $this->formatMessage($message)
                ]);
            }

            try {
                $aiText = $this->generateAiReply($conversation);
                $aiUserId = $this->getAiUserIdFromConversation($conversation);

                $aiMessage = Message::create([
                    'conversation_id' => $conversation->id,
                    'user_id' => $aiUserId,
                    'message' => $aiText,
                    'type' => 'text',
                    'is_read' => true
                ]);

                $conversation->touch();
                $aiMessage->load('user');

                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado',
                    'data' => $this->formatMessage($message),
                    'ai_message' => $this->formatMessage($aiMessage)
                ]);
            } catch (\Throwable $aiError) {
                Log::error('Error respondiendo con IA: ' . $aiError->getMessage(), [
                    'conversation_id' => $conversation->id
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Mensaje enviado, pero la IA no pudo responder en este momento.',
                    'data' => $this->formatMessage($message),
                    'ai_error' => $aiError->getMessage()
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

    public function sendFile(Request $request)
{
    try {
        $request->validate([
            'conversation_id' => 'required|exists:conversations,id',
            'file' => 'required|file|mimes:jpeg,png,jpg,gif,webp,mp4,mov,avi,webm|max:20480'
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

        $type = str_starts_with((string) $mime, 'image/') ? 'image' : 'video';
        $folder = $type === 'image' ? 'images' : 'videos';

        $year = date('Y');
        $month = date('m');

        $extension = strtolower($file->getClientOriginalExtension());
        $filename = uniqid($type . '_', true) . '.' . $extension;

        $relativeDir = "chat-files/{$folder}/{$year}/{$month}";
        $relativePath = $relativeDir . '/' . $filename;
        $destination = public_path('storage/' . $relativeDir);

        if (!is_dir($destination)) {
            mkdir($destination, 0755, true);
        }

        $file->move($destination, $filename);

        $message = Message::create([
            'conversation_id' => $request->conversation_id,
            'user_id' => Auth::id(),
            'type' => $type,
            'file_path' => $relativePath,
            'message' => null,
            'is_read' => false
        ]);

        $conversation->touch();
        $message->load('user');

        return response()->json([
            'success' => true,
            'message' => 'Archivo enviado',
            'data' => $this->formatMessage($message)
        ]);
    } catch (\Exception $e) {
        Log::error('Error enviando archivo: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Error al enviar el archivo: ' . $e->getMessage()
        ], 500);
    }
}

    public function markAsRead(Request $request)
    {
        try {
            $request->validate([
                'conversation_id' => 'required|exists:conversations,id'
            ]);

            $conversation = Conversation::findOrFail($request->conversation_id);

            if (!$this->userBelongsToConversation(Auth::id(), $conversation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para esta conversación.'
                ], 403);
            }

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

    public function getNewMessages($conversationId, $lastMessageId)
    {
        try {
            $conversation = Conversation::findOrFail($conversationId);

            if (!$this->userBelongsToConversation(Auth::id(), $conversation)) {
                return response()->json([
                    'error' => 'No autorizado'
                ], 403);
            }

            $messages = Message::where('conversation_id', $conversationId)
                ->where('id', '>', $lastMessageId)
                ->with('user')
                ->orderBy('created_at', 'asc')
                ->get()
                ->map(fn($message) => $this->formatMessage($message));

            return response()->json($messages);
        } catch (\Exception $e) {
            Log::error('Error obteniendo mensajes nuevos: ' . $e->getMessage());
            return response()->json(['error' => 'Error'], 500);
        }
    }

    private function formatMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->name,
            'user_avatar' => $message->user?->avatar,
            'user_avatar_url' => $this->avatarUrl($message->user?->avatar),
            'message' => $message->message,
            'type' => $message->type ?? 'text',
            'file_path' => $message->file_path,
            'file_url' => $this->mediaUrl($message->file_path),
            'is_read' => (bool) $message->is_read,
            'created_at' => optional($message->created_at)->toISOString(),
            'updated_at' => optional($message->updated_at)->toISOString(),
        ];
    }
   
    private function mediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
    
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
    
        return asset('storage/' . ltrim($path, '/'));
    }
    
    private function avatarUrl(?string $avatar, string $name = 'Usuario'): string
    {
        if (!$avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=40&background=25D366&color=fff';
        }
    
        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }
    
        return asset('storage/' . ltrim($avatar, '/'));
    }

    private function userBelongsToConversation(int $userId, Conversation $conversation): bool
    {
        return (int) $conversation->user_one === $userId
            || (int) $conversation->user_two === $userId;
    }

    private function isAiConversation(Conversation $conversation): bool
    {
        $aiBotUserId = (int) config('services.gemini.bot_user_id');

        if (!$aiBotUserId) {
            return false;
        }

        return (int) $conversation->user_one === $aiBotUserId
            || (int) $conversation->user_two === $aiBotUserId;
    }

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

        $response = Http::timeout(60)
            ->withHeaders([
                'x-goog-api-key' => $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent",
                $payload
            );

        if (!$response->successful()) {
            Log::error('Gemini HTTP error', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            throw new \Exception("Gemini devolvió HTTP {$response->status()}");
        }

        $json = $response->json();

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

        return $text;
    }

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

    private function getSidebarConversations(int $userId)
    {
        return Conversation::where(function ($query) use ($userId) {
            $query->where('user_one', $userId)
                ->orWhere('user_two', $userId);
        })
            ->with(['userOne', 'userTwo', 'lastMessage'])
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    private function getSidebarGroups(int $userId)
    {
        return Group::whereHas('users', function ($query) use ($userId) {
            $query->where('users.id', $userId);
        })
            ->with(['users', 'lastMessage.user'])
            ->withCount('users')
            ->orderBy('updated_at', 'desc')
            ->get();
    }

    public function searchUsers(Request $request)
    {
        try {
            $search = trim((string) $request->get('q', ''));

            if (mb_strlen($search) < 2) {
                return response()->json([]);
            }

            $users = User::query()
                ->where('id', '!=', Auth::id())
                ->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->limit(10)
                ->get(['id', 'name', 'email', 'avatar', 'is_online'])
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'avatar' => $user->avatar,
                        'avatar_url' => $this->avatarUrl($user->avatar, $user->name),
                        'is_online' => (bool) $user->is_online,
                    ];
                })
                ->values();

            return response()->json($users);
        } catch (\Throwable $e) {
            Log::error('Error en searchUsers: ' . $e->getMessage(), [
                'query' => $request->get('q'),
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error en la búsqueda de usuarios.'
            ], 500);
        }
    }

    
}
