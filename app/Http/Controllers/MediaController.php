<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function show(string $path)
    {
        $path = ltrim($path, '/');
        $path = str_replace(['../', '..\\'], '', $path);

        // Solo permitir carpetas esperadas
        $allowedPrefixes = [
            'avatars/',
            'chat-files/',
        ];

        $isAllowed = collect($allowedPrefixes)->contains(function ($prefix) use ($path) {
            return str_starts_with($path, $prefix);
        });

        abort_unless($isAllowed, 403, 'Ruta no permitida.');

        // Opción 1: storage/app/public
        if (Storage::disk('public')->exists($path)) {
            $fullPath = Storage::disk('public')->path($path);

            return response()->file($fullPath, [
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }

        // Opción 2: public/storage
        $publicStoragePath = public_path('storage/' . $path);

        if (file_exists($publicStoragePath) && is_file($publicStoragePath)) {
            return response()->file($publicStoragePath, [
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }

        abort(404, 'Archivo no encontrado.');
    }
}