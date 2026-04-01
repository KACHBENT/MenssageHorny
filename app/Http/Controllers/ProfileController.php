<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

   public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => ['required', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:5120'],
            ]);
    
            $user = Auth::user();
    
            // Eliminar avatar anterior si era local
            if (!empty($user->avatar) && !$this->isExternalUrl($user->avatar)) {
                $oldRelativePath = ltrim($user->avatar, '/');
                $oldFullPath = public_path('storage/' . $oldRelativePath);
    
                if (file_exists($oldFullPath) && is_file($oldFullPath)) {
                    @unlink($oldFullPath);
                }
            }
    
            $file = $request->file('avatar');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = uniqid('avatar_', true) . '.' . $extension;
    
            $relativeDir = 'avatars';
            $relativePath = $relativeDir . '/' . $filename;
            $destination = public_path('storage/' . $relativeDir);
    
            if (!is_dir($destination)) {
                mkdir($destination, 0755, true);
            }
    
            $file->move($destination, $filename);
    
            // Guardar SOLO ruta relativa en BD
            $user->avatar = $relativePath;
            $user->save();
    
            return response()->json([
                'success' => true,
                'message' => 'Avatar actualizado correctamente',
                'avatar' => $user->avatar,
                'avatar_url' => asset('storage/' . $relativePath),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error actualizando avatar: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);
    
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    private function isExternalUrl(?string $value): bool
    {
        if (!$value) {
            return false;
        }
    
        return str_starts_with($value, 'http://') || str_starts_with($value, 'https://');
    }

    public function updateInfo(Request $request)
    {
        try {
            $user = Auth::user();

            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            ]);

            $user->name = trim((string) $request->name);
            $user->email = trim((string) $request->email);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Información actualizada correctamente',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                    'avatar_url' => $this->avatarUrl($user->avatar, $user->name),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error actualizando perfil: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'new_password' => ['required', 'string', 'min:6', 'confirmed'],
            ]);

            $user = Auth::user();
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente',
            ]);
        } catch (\Throwable $e) {
            Log::error('Error actualizando contraseña: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la contraseña',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getProfile()
    {
        $user = Auth::user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar,
            'avatar_url' => $this->avatarUrl($user->avatar, $user->name),
            'is_online' => $user->is_online,
            'last_seen' => $user->last_seen,
        ]);
    }

    private function avatarUrl(?string $avatar, string $name = 'Usuario'): string
    {
        if (!$avatar) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=120&background=25D366&color=fff';
        }

        if ($this->isExternalUrl($avatar)) {
            return $avatar;
        }

        return asset('storage/' . ltrim($avatar, '/'));
    }

}