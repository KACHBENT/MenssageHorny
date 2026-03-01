<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Constructor - aplicar middleware de autenticación
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar formulario de edición de perfil
     */
    public function edit()
    {
        $user = Auth::user();
        return view('profile.edit', compact('user'));
    }

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
            ], 500);
        }
    }

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
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Información actualizada correctamente',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la información: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar contraseña
     */
    public function updatePassword(Request $request)
    {
        try {
            $request->validate([
                'current_password' => 'required|current_password',
                'new_password' => 'required|string|min:6|confirmed'
            ]);

            $user = Auth::user();
            $user->password = Hash::make($request->new_password);
            $user->save();

            return response()->json([
                'success' => true,
                'message' => 'Contraseña actualizada correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la contraseña: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos del perfil
     */
    public function getProfile()
    {
        $user = Auth::user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'avatar' => $user->avatar ? Storage::url($user->avatar) : null,
            'is_online' => $user->is_online,
            'last_seen' => $user->last_seen
        ]);
    }
}