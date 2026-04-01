<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BiometricAuthLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use App\Models\BiometricCredential;

class BiometricController extends Controller
{
    public function settings()
    {
        $credentials = Auth::user()
            ->biometricCredentials()
            ->latest()
            ->get();

        return view('auth.biometric-settings', compact('credentials'));
    }

    public function enrollStart(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:120'],
        ]);

        $challengeId = (string) Str::uuid();
        $nonce = random_bytes(32);

        Cache::put("bio:enroll:$challengeId", [
            'user_id' => Auth::id(),
            'device_id' => $request->device_id,
            'device_name' => $request->device_name,
            'nonce_b64' => base64_encode($nonce),
        ], now()->addMinutes(3));

        return response()->json([
            'challenge_id' => $challengeId,
            'nonce' => base64_encode($nonce),
            'purpose' => 'enroll',
        ]);
    }

    public function enrollFinish(Request $request)
    {
        $request->validate([
            'challenge_id' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:100'],
            'device_name' => ['nullable', 'string', 'max:120'],
            'key_alias' => ['required', 'string', 'max:150'],
            'public_key_pem' => ['required', 'string'],
            'signature_b64' => ['required', 'string'],
        ]);

        $payload = Cache::pull("bio:enroll:{$request->challenge_id}");

        if (!$payload || (int)$payload['user_id'] !== (int)Auth::id()) {
            return response()->json(['message' => 'Reto inválido o expirado.'], 422);
        }

        if ($payload['device_id'] !== $request->device_id) {
            return response()->json(['message' => 'Dispositivo no coincide.'], 422);
        }

        $publicKey = openssl_pkey_get_public($request->public_key_pem);
        if (!$publicKey) {
            return response()->json(['message' => 'Llave pública inválida.'], 422);
        }

        $verified = openssl_verify(
            base64_decode($payload['nonce_b64']),
            base64_decode($request->signature_b64),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            BiometricAuthLog::create([
                'user_id' => Auth::id(),
                'event' => 'enroll_failed',
                'device_id' => $request->device_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'notes' => 'Firma inválida en registro biométrico',
            ]);

            return response()->json(['message' => 'No se pudo validar la firma biométrica.'], 422);
        }

        $credential = BiometricCredential::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'device_id' => $request->device_id,
            ],
            [
                'device_name' => $request->device_name,
                'key_alias' => $request->key_alias,
                'public_key_pem' => $request->public_key_pem,
                'enabled' => true,
                'last_used_at' => now(),
            ]
        );

        BiometricAuthLog::create([
            'user_id' => Auth::id(),
            'biometric_credential_id' => $credential->id,
            'event' => 'enroll_success',
            'device_id' => $request->device_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'notes' => 'Huella registrada correctamente',
        ]);

        return response()->json([
            'message' => 'Huella registrada correctamente.',
            'credential_id' => $credential->id,
        ]);
    }

    public function loginStart(Request $request)
    {
        $request->validate([
            'device_id' => ['required', 'string', 'max:100'],
        ]);
        
        $credential = BiometricCredential::where('device_id', $request->device_id)
            ->where('enabled', true)
            ->first();
        
        if (!$credential) {
            return response()->json([
                'message' => 'No hay huella registrada para este dispositivo.'
            ], 404);
        }
        
        $challengeId = (string) \Illuminate\Support\Str::uuid();
        $nonce = random_bytes(32);
        
        \Illuminate\Support\Facades\Cache::put("bio:login:$challengeId", [
            'user_id' => $credential->user_id,
            'credential_id' => $credential->id,
            'device_id' => $request->device_id,
            'nonce_b64' => base64_encode($nonce),
        ], now()->addMinutes(3));
        
        return response()->json([
            'challenge_id' => $challengeId,
            'nonce' => base64_encode($nonce),
            'key_alias' => $credential->key_alias,
            'purpose' => 'login',
        ]);
    }

    public function loginVerify(Request $request)
    {
        $request->validate([
            'challenge_id' => ['required', 'string'],
            'device_id' => ['required', 'string', 'max:100'],
            'signature_b64' => ['required', 'string'],
        ]);

        $payload = Cache::pull("bio:login:{$request->challenge_id}");

        if (!$payload) {
            return response()->json(['message' => 'Reto inválido o expirado.'], 422);
        }

        if ($payload['device_id'] !== $request->device_id) {
            return response()->json(['message' => 'Dispositivo no coincide.'], 422);
        }

        $credential = BiometricCredential::find($payload['credential_id']);

        if (!$credential || !$credential->enabled) {
            return response()->json(['message' => 'Credencial biométrica no disponible.'], 422);
        }

        $publicKey = openssl_pkey_get_public($credential->public_key_pem);
        if (!$publicKey) {
            return response()->json(['message' => 'Llave pública inválida.'], 422);
        }

        $verified = openssl_verify(
            base64_decode($payload['nonce_b64']),
            base64_decode($request->signature_b64),
            $publicKey,
            OPENSSL_ALGO_SHA256
        );

        if ($verified !== 1) {
            BiometricAuthLog::create([
                'user_id' => $payload['user_id'],
                'biometric_credential_id' => $credential->id,
                'event' => 'login_failed',
                'device_id' => $request->device_id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'notes' => 'Firma inválida en login biométrico',
            ]);

            return response()->json(['message' => 'No se pudo validar la firma biométrica.'], 422);
        }

        Auth::loginUsingId($payload['user_id'], true);

        $credential->update([
            'last_used_at' => now(),
        ]);

        BiometricAuthLog::create([
            'user_id' => $payload['user_id'],
            'biometric_credential_id' => $credential->id,
            'event' => 'login_success',
            'device_id' => $request->device_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'notes' => 'Login con huella correcto',
        ]);

        return response()->json([
            'message' => 'Autenticación biométrica correcta.',
            'redirect' => url('/'),
        ]);
    }

    public function disable(Request $request, BiometricCredential $credential)
    {
        abort_unless($credential->user_id === Auth::id(), 403);

        $credential->update(['enabled' => false]);

        BiometricAuthLog::create([
            'user_id' => Auth::id(),
            'biometric_credential_id' => $credential->id,
            'event' => 'disabled',
            'device_id' => $credential->device_id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'notes' => 'Credencial biométrica deshabilitada',
        ]);

        return back()->with('success', 'Huella deshabilitada.');
    }
}