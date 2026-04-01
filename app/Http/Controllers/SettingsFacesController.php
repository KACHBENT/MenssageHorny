<?php

namespace App\Http\Controllers;

use App\Models\FaceBiometric;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SettingsFacesController extends Controller
{
    public function settingFaceProfile()
    {
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Debes iniciar sesión para configurar tu rostro.');
        }

        $user = Auth::user()->load('faceBiometric');

        return view('settingfaces.settingfaceprofile', compact('user'));
    }

    public function loginAccessFace()
    {
        if (auth()->check()) {
            return redirect()->route('chats.index');
        }

        return view('settingfaces.loginaccessface');
    }

    public function saveFaceProfile(Request $request)
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Debes iniciar sesión.'
            ], 401);
        }

        $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
        ]);

        try {
            $user = Auth::user();

            $descriptor = $this->normalizeDescriptor($request->input('descriptor', []));

            $faceBiometric = FaceBiometric::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'face_descriptor' => json_encode($descriptor, JSON_UNESCAPED_UNICODE),
                    'is_enabled' => true,
                    'registered_at' => now(),
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Rostro registrado correctamente.',
                'data' => [
                    'id' => $faceBiometric->id,
                    'user_id' => $faceBiometric->user_id,
                    'is_enabled' => (bool) $faceBiometric->is_enabled,
                    'registered_at' => optional($faceBiometric->registered_at)->toDateTimeString(),
                ]
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al guardar perfil facial: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo guardar el perfil facial.'
            ], 500);
        }
    }

    public function removeFaceProfile()
    {
        if (!auth()->check()) {
            return response()->json([
                'success' => false,
                'message' => 'No autorizado. Debes iniciar sesión.'
            ], 401);
        }

        try {
            $user = Auth::user();

            $faceBiometric = FaceBiometric::where('user_id', $user->id)->first();

            if (!$faceBiometric) {
                return response()->json([
                    'success' => false,
                    'message' => 'No existe registro facial para este usuario.'
                ], 404);
            }

            $faceBiometric->delete();

            return response()->json([
                'success' => true,
                'message' => 'Perfil facial eliminado correctamente.'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al eliminar perfil facial: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el perfil facial.'
            ], 500);
        }
    }

    public function verifyFaceLogin(Request $request)
    {
        if (auth()->check()) {
            return response()->json([
                'success' => true,
                'message' => 'Ya tienes una sesión activa.',
                'redirect' => route('chats.index'),
            ]);
        }

        $request->validate([
            'descriptor' => ['required', 'array', 'size:128'],
            'descriptor.*' => ['required', 'numeric'],
            'identity' => ['nullable', 'string', 'max:150'],
            'remember' => ['nullable', 'boolean'],
        ]);

        try {
            $probe = $this->normalizeDescriptor($request->input('descriptor', []));
            $identity = trim((string) $request->input('identity', ''));
            $remember = (bool) $request->boolean('remember', false);

            $query = FaceBiometric::query()
                ->with('user')
                ->where('is_enabled', true)
                ->whereNotNull('face_descriptor');

            if ($identity !== '') {
                $query->whereHas('user', function ($q) use ($identity) {
                    $q->where('email', $identity)
                      ->orWhere('name', $identity);
                });
            }

            $records = $query->get();

            if ($records->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay perfiles faciales disponibles para validar.'
                ], 422);
            }

            $bestRecord = null;
            $bestDistance = INF;

            foreach ($records as $record) {
                $stored = json_decode((string) $record->face_descriptor, true);

                if (!is_array($stored) || count($stored) !== 128) {
                    continue;
                }

                $distance = $this->euclideanDistance($probe, $stored);

                if ($distance < $bestDistance) {
                    $bestDistance = $distance;
                    $bestRecord = $record;
                }
            }

            $threshold = 0.48;

            if (!$bestRecord || $bestDistance > $threshold || !$bestRecord->user) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró coincidencia facial válida.',
                    'distance' => is_finite($bestDistance) ? round($bestDistance, 6) : null,
                ], 422);
            }

            $bestRecord->update([
                'last_verified_at' => now(),
            ]);

            Auth::login($bestRecord->user, $remember);
            $request->session()->regenerate();

            return response()->json([
                'success' => true,
                'message' => 'Autenticación facial correcta.',
                'distance' => round($bestDistance, 6),
                'redirect' => route('chats.index'),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en login facial: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'No se pudo validar el acceso facial.'
            ], 500);
        }
    }

    private function normalizeDescriptor(array $descriptor): array
    {
        return array_map(function ($value) {
            return round((float) $value, 8);
        }, array_values($descriptor));
    }

    private function euclideanDistance(array $a, array $b): float
    {
        if (count($a) !== count($b) || count($a) === 0) {
            return INF;
        }

        $sum = 0.0;

        foreach ($a as $i => $value) {
            $diff = (float) $value - (float) $b[$i];
            $sum += $diff * $diff;
        }

        return sqrt($sum);
    }
}