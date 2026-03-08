<?php

namespace App\Http\Controllers;

use App\Mail\UserCredentialsMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Throwable;

class AuthController extends Controller
{
    /**
     * Mostrar formulario de login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Procesar login
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->is_online = true;
            $user->last_seen = now();
            $user->save();

            return redirect()->intended('/chats');
        }

        return back()
            ->withErrors([
                'email' => 'Las credenciales no coinciden con nuestros registros.',
            ])
            ->onlyInput('email');
    }

    /**
     * Cerrar sesión
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            $user->is_online = false;
            $user->last_seen = now();
            $user->save();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }

    /**
     * Mostrar formulario de registro
     */
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    /**
     * Procesar registro:
     * - genera contraseña temporal
     * - crea usuario
     * - envía credenciales por correo
     */
  public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'  => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
        'terms' => ['accepted'],
    ], [
        'name.required' => 'El nombre es obligatorio.',
        'name.max' => 'El nombre no debe exceder 255 caracteres.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'Ingresa un correo válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'terms.accepted' => 'Debes aceptar los términos y condiciones.',
    ]);

    if ($validator->fails()) {
        return back()->withErrors($validator)->withInput();
    }

    $user = null;

    try {
        $plainPassword = $this->generateTemporaryPassword();

        DB::beginTransaction();

        $user = User::create([
            'name' => trim($request->name),
            'email' => trim($request->email),
            'password' => Hash::make($plainPassword),
            'is_online' => false,
            'last_seen' => now(),
        ]);

        DB::commit();

        Mail::to($user->email)->send(new UserCredentialsMail($user, $plainPassword));

        return redirect()
            ->route('login')
            ->with('success', 'Usuario registrado correctamente. Las credenciales fueron enviadas al correo.');
    } catch (\Throwable $e) {
        DB::rollBack();

        if ($user && $user->exists) {
            try {
                $user->delete();
            } catch (\Throwable $deleteException) {
            }
        }

        return back()
            ->with('error', 'No se pudo enviar el correo. Verifica la configuración SMTP. Error: ' . $e->getMessage())
            ->withInput();
    }
}

    /**
     * Genera una contraseña temporal segura
     */
    private function generateTemporaryPassword(int $length = 12): string
    {
        $uppercase = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
        $lowercase = 'abcdefghijkmnopqrstuvwxyz';
        $numbers   = '23456789';
        $symbols   = '@$!%*#?&';

        $all = $uppercase . $lowercase . $numbers . $symbols;

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $symbols[random_int(0, strlen($symbols) - 1)];

        for ($i = 4; $i < $length; $i++) {
            $password .= $all[random_int(0, strlen($all) - 1)];
        }

        return str_shuffle($password);
    }
}