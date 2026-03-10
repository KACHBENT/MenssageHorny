<?php

namespace App\Http\Controllers;

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
}