<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use Illuminate\Http\Request;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    /**
     * Crear un nuevo grupo
     */
    public function createGroup(Request $request)
    {
        // Validación
        $request->validate([
            'name' => 'required|string|max:255'
        ]);

        // Crear grupo
        $group = Group::create([
            'name' => $request->name,
            'creator_id' => Auth::id()
        ]);

        // Agregar creador al grupo como admin
        $group->users()->attach(Auth::id(), [
            'role' => 'admin'
        ]);

        return response()->json([
            'message' => 'Grupo creado correctamente',
            'group' => $group
        ]);
    }
=======
use App\Models\Conversation;
use App\Models\Group;
use App\Models\GroupMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['integer', 'exists:users,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $creatorId = Auth::id();

        $userIds = collect($request->user_ids)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0 && $id !== $creatorId)
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Debes seleccionar al menos un usuario.',
            ], 422);
        }

        $group = DB::transaction(function () use ($request, $creatorId, $userIds) {
            $group = Group::create([
                'name' => $request->name,
                'creator_id' => $creatorId,
                'description' => $request->description,
            ]);

            $group->users()->attach($creatorId, [
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($userIds as $userId) {
                $group->users()->attach($userId, [
                    'role' => 'member',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            return $group;
        });

        return response()->json([
            'success' => true,
            'message' => 'Grupo creado correctamente.',
            'group' => $group->load('users'),
            'redirect' => route('groups.show', $group->id),
        ]);
    }

    public function show(Group $group)
    {
        $this->authorizeMember($group);

        $group->load([
            'creator',
            'users',
            'messages.user',
        ]);

        $messages = $group->messages()
            ->with('user')
            ->orderBy('id', 'asc')
            ->get();

        $groups = Group::whereHas('users', function ($q) {
                $q->where('users.id', Auth::id());
            })
            ->with(['users', 'lastMessage.user'])
            ->withCount('users')
            ->orderByDesc('updated_at')
            ->get();

        $conversations = Conversation::with(['userOne', 'userTwo', 'lastMessage'])
            ->where(function ($q) {
                $q->where('user_one', Auth::id())
                  ->orWhere('user_two', Auth::id());
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('groups.show', compact(
            'group',
            'messages',
            'groups',
            'conversations'
        ));
    }

    public function sendMessage(Request $request, Group $group)
    {
        $this->authorizeMember($group);

        $request->validate([
            'message' => ['required', 'string'],
        ]);

        $message = GroupMessage::create([
            'group_id' => $group->id,
            'user_id' => Auth::id(),
            'message' => $request->message,
            'type' => 'text',
        ]);

        $group->touch();
        $message->load('user');

        return response()->json([
            'success' => true,
            'data' => $this->formatMessage($message),
        ]);
    }

    public function sendFile(Request $request, Group $group)
    {
        $this->authorizeMember($group);

        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,gif,webp,mp4,mov,avi,webm'],
        ]);

        try {
            $file = $request->file('file');
            $mime = (string) $file->getMimeType();

            $type = str_starts_with($mime, 'image/') ? 'image' : 'video';

            $folder = $type === 'image'
                ? 'chat-files/images/' . now()->format('Y/m')
                : 'chat-files/videos/' . now()->format('Y/m');

            $extension = strtolower($file->getClientOriginalExtension());
            $filename = uniqid($type . '_', true) . '.' . $extension;

            // Guardar físicamente en public/storage/... para que asset('storage/...') funcione sin symlink
            $destination = public_path('storage/' . $folder);

            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }

            $file->move($destination, $filename);

            $relativePath = trim($folder . '/' . $filename, '/');

            $message = GroupMessage::create([
                'group_id' => $group->id,
                'user_id' => Auth::id(),
                'message' => null,
                'type' => $type,
                'file_path' => $relativePath,
            ]);

            $group->touch();
            $message->load('user');

            return response()->json([
                'success' => true,
                'data' => $this->formatMessage($message),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error enviando archivo de grupo: ' . $e->getMessage(), [
                'group_id' => $group->id,
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo enviar el archivo.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function messagesSince(Group $group, $lastId)
    {
        $this->authorizeMember($group);

        $messages = $group->messages()
            ->with('user')
            ->where('id', '>', (int) $lastId)
            ->orderBy('id', 'asc')
            ->get()
            ->map(fn ($message) => $this->formatMessage($message));

        return response()->json($messages);
    }

    private function authorizeMember(Group $group): void
    {
        $isMember = $group->users()
            ->where('users.id', Auth::id())
            ->exists();

        abort_unless($isMember, 403, 'No perteneces a este grupo.');
    }

    private function formatMessage(GroupMessage $message): array
    {
        return [
            'id' => $message->id,
            'group_id' => $message->group_id,
            'user_id' => $message->user_id,
            'user_name' => $message->user?->name,
            'user_avatar' => $message->user?->avatar,
            'user_avatar_url' => $this->publicAvatarUrl(
                $message->user?->avatar,
                $message->user?->name ?? 'Usuario'
            ),
            'message' => $message->message,
            'type' => $message->type,
            'file_path' => $message->file_path,
            'file_url' => $this->publicMediaUrl($message->file_path),
            'created_at' => optional($message->created_at)->toISOString(),
            'updated_at' => optional($message->updated_at)->toISOString(),
        ];
    }

    private function publicMediaUrl(?string $path): ?string
    {
        if (!$path) {
            return null;
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return asset('storage/' . ltrim($path, '/'));
    }

    private function publicAvatarUrl(?string $avatar, string $name = 'Usuario'): string
    {
        if (!$avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=40&background=25D366&color=fff';
        }

        if (str_starts_with($avatar, 'http://') || str_starts_with($avatar, 'https://')) {
            return $avatar;
        }

        return asset('storage/' . ltrim($avatar, '/'));
    }
>>>>>>> main
}