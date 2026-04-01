<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;
<<<<<<< HEAD
 use App\Http\Controllers\GroupController;
=======
use App\Http\Controllers\GroupController;
use App\Http\Controllers\SettingsFacesController;
use App\Http\Controllers\BiometricController;
use App\Http\Controllers\MediaController;
>>>>>>> main

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (accesibles sin autenticación)
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/chats');
    }
    return redirect('/login');
});

<<<<<<< HEAD
=======
Route::get('/media/{path}', [MediaController::class, 'show'])
    ->where('path', '.*')
    ->name('media.show');

>>>>>>> main
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

<<<<<<< HEAD
=======
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])
    ->name('password.request');

Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])
    ->name('password.email');

Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])
    ->name('password.reset.form');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])
    ->name('password.update');

>>>>>>> main

// Opcional: rutas de registro
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {

    // Chats
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
<<<<<<< HEAD
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');

    // API de mensajes (para AJAX)
    Route::post('/messages/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/send-file', [ChatController::class, 'sendFile'])->name('messages.send-file');
    Route::get('/messages/unread', [ChatController::class, 'getUnreadCount'])->name('messages.unread');
    Route::post('/messages/mark-read', [ChatController::class, 'markAsRead'])->name('messages.mark-read');
    Route::post('/groups/create', [GroupController::class, 'createGroup']);
   

Route::post('/groups/create',[GroupController::class,'createGroup'])->middleware('auth');
=======
    Route::get('/chat/{userId}', [ChatController::class, 'show'])->name('chat.show');
    Route::get('/chat/search/users', [ChatController::class, 'searchUsers'])
        ->name('chat.search');

    Route::post('/messages/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/send-file', [ChatController::class, 'sendFile'])->name('messages.send-file');
    Route::post('/messages/mark-read', [ChatController::class, 'markAsRead'])->name('messages.mark-read');

    Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
    Route::get('/groups/{group}', [GroupController::class, 'show'])->name('groups.show');
    Route::post('/groups/{group}/messages', [GroupController::class, 'sendMessage'])->name('groups.messages.send');
    Route::post('/groups/{group}/messages/file', [GroupController::class, 'sendFile'])->name('groups.messages.send-file');
    Route::get('/groups/{group}/messages/since/{lastId}', [GroupController::class, 'messagesSince'])->name('groups.messages.since');
>>>>>>> main
    // Búsqueda
    Route::get('/search/users', [ChatController::class, 'searchUsers'])->name('chat.search');

    Route::get('/chat-ia', function () {
        $botUserId = config('services.gemini.bot_user_id');
        return redirect()->route('chat.show', $botUserId);
    })->name('chat.ia');

    // Perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::post('/profile/update-info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/data', [ProfileController::class, 'getProfile'])->name('profile.data');
<<<<<<< HEAD

    Route::get('/test-mail', function () {
        try {
            Mail::raw('Correo de prueba desde Laravel', function ($message) {
                $message->to('consultingtechnova4@gmail.com')
                    ->subject('Prueba SMTP Laravel');
            });

            return 'Correo enviado correctamente';
        } catch (\Throwable $e) {
            return 'Error al enviar: ' . $e->getMessage();
        }
    });


});
// Obtener mensajes nuevos desde un ID
Route::get('/chat/messages/{conversation}/since/{lastMessageId}', function ($conversation, $lastMessageId) {
    $messages = App\Models\Message::where('conversation_id', $conversation)
        ->where('id', '>', $lastMessageId)
        ->orderBy('created_at', 'asc')
        ->get();



    return response()->json($messages);
})->middleware('auth')->name('messages.new');

=======
});


Route::get('/settings-faces/settingface-profile', [SettingsFacesController::class, 'settingFaceProfile'])
    ->name('settingfaces.settingfaceprofile');

Route::post('/settings-faces/save-face-profile', [SettingsFacesController::class, 'saveFaceProfile'])
    ->name('settingfaces.savefaceprofile');

Route::delete('/settings-faces/remove-face-profile', [SettingsFacesController::class, 'removeFaceProfile'])
    ->name('settingfaces.removefaceprofile');

Route::get('/login-face/start-login', [SettingsFacesController::class, 'loginAccessFace'])
    ->name('settingfaces.loginaccessface');

Route::post('/login-face/verify', [SettingsFacesController::class, 'verifyFaceLogin'])
    ->name('settingfaces.verifyfacelogin');

Route::get('/login-face/start-login', [SettingsFacesController::class, 'loginAccessFace'])
    ->name('settingfaces.loginaccessface');

Route::post('/login-face/verify', [SettingsFacesController::class, 'verifyFaceLogin'])
    ->name('settingfaces.verifyfacelogin');



Route::get('/test-mail', function () {
    try {
        Mail::raw('Correo de prueba desde Laravel', function ($message) {
            $message->to('consultingtechnova4@gmail.com')
                ->subject('Prueba SMTP Laravel');
        });

        return 'Correo enviado correctamente';
    } catch (\Throwable $e) {
        return 'Error al enviar: ' . $e->getMessage();
    }
});


//! Biometricos acceso y configuraciones
Route::middleware('auth')->group(function () {
    Route::get('/biometrico/configuracion', [BiometricController::class, 'settings'])
        ->name('biometric.settings');

    Route::post('/biometrico/enroll/start', [BiometricController::class, 'enrollStart'])
        ->name('biometric.enroll.start');

    Route::post('/biometrico/enroll/finish', [BiometricController::class, 'enrollFinish'])
        ->name('biometric.enroll.finish');

    Route::post('/biometrico/disable/{credential}', [BiometricController::class, 'disable'])
        ->name('biometric.disable');
});

Route::get('/biometrico/login', function () {
    return view('auth.biometric-login');
})->name('biometric.login');

Route::post('/biometrico/login/start', [BiometricController::class, 'loginStart'])
    ->name('biometric.login.start');

Route::post('/biometrico/login/verify', [BiometricController::class, 'loginVerify'])
    ->name('biometric.login.verify');


// Obtener mensajes nuevos desde un ID
Route::get('/chat/messages/{conversation}/since/{lastMessageId}', [ChatController::class, 'getNewMessages'])
    ->middleware('auth')
    ->name('messages.new');
>>>>>>> main

Route::get('/download-apk', function () {
    $path = public_path('apk/chat.apk');

    if (!file_exists($path)) {
        abort(404);
    }

    return response()->download($path, 'MenssageHornyChat.apk');
<<<<<<< HEAD
});
=======
});
>>>>>>> main
