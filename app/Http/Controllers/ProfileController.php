<?php

namespace App\Http\Controllers;

<<<<<<< HEAD
use App\Models\User;
=======
>>>>>>> main
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
<<<<<<< HEAD

class ProfileController extends Controller
{
    /**
     * Constructor - aplicar middleware de autenticación
     */
=======
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
>>>>>>> main
    public function __construct()
    {
        $this->middleware('auth');
    }

<<<<<<< HEAD
    /**
     * Mostrar formulario de edición de perfil
     */
=======
>>>>>>> main
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

<<<<<<< HEAD
    /**
     * Actualizar avatar
     */
    public function updateAvatar(Request $request)
    {
        try {
            $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
            ]);

            $user = Auth::user();
            
            // Eliminar avatar anterior si existe
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Guardar nuevo avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            
            $user->avatar = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Avatar actualizado correctamente',
                'avatar_url' => Storage::url($path)
            ]);

        } catch (\Exception $e) {
            Log::error('Error actualizando avatar: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el avatar: ' . $e->getMessage()
=======
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
>>>>>>> main
            ], 500);
        }
    }

<<<<<<< HEAD
    /**
     * Actualizar información del perfil
     */
    public function updateInfo(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email,' . Auth::id()
            ]);

            $user = Auth::user();
            $user->name = $request->name;
            $user->email = $request->email;
=======
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
>>>>>>> main
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Información actualizada correctamente',
<<<<<<< HEAD
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información: ' . $e->getMessage()
=======
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
>>>>>>> main
            ], 500);
        }
    }

<<<<<<< HEAD
    /**
     * Actualizar contraseña
     */
=======
>>>>>>> main
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
<<<<<<< HEAD
                'current_password' => 'required|current_password',
                'new_password' => 'required|string|min:6|confirmed'
=======
                'current_password' => ['required', 'current_password'],
                'new_password' => ['required', 'string', 'min:6', 'confirmed'],
>>>>>>> main
            ]);

            $user = Auth::user();
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
<<<<<<< HEAD
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la contraseña: ' . $e->getMessage()
=======
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
>>>>>>> main
            ], 500);
        }
    }

<<<<<<< HEAD
    /**
     * Obtener datos del perfil
     */
    public function getProfile()
    {
        $user = Auth::user();
        
=======
    public function getProfile()
    {
        $user = Auth::user();

>>>>>>> main
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
<<<<<<< HEAD
            'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
            'is_online' => $user->is_online,
            'last_seen' => $user->last_seen
        ]);
    }
=======
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

>>>>>>> main
}