<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ProfileController;

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

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Opcional: rutas de registro
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

// Rutas protegidas (requieren autenticación)
Route::middleware(['auth'])->group(function () {
    
    // Chats
    Route::get('/chats', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/chat/{user}', [ChatController::class, 'show'])->name('chat.show');
    
    // API de mensajes (para AJAX)
    Route::post('/messages/send', [ChatController::class, 'sendMessage'])->name('messages.send');
    Route::post('/messages/send-file', [ChatController::class, 'sendFile'])->name('messages.send-file');
    Route::get('/messages/unread', [ChatController::class, 'getUnreadCount'])->name('messages.unread');
    Route::post('/messages/mark-read', [ChatController::class, 'markAsRead'])->name('messages.mark-read');
    
    // Búsqueda
    Route::get('/search/users', [ChatController::class, 'searchUsers'])->name('chat.search');
    
    // Perfil
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile/update-avatar', [ProfileController::class, 'updateAvatar'])->name('profile.update-avatar');
    Route::post('/profile/update-info', [ProfileController::class, 'updateInfo'])->name('profile.update-info');
    Route::post('/profile/update-password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::get('/profile/data', [ProfileController::class, 'getProfile'])->name('profile.data');
});
// Obtener mensajes nuevos desde un ID
Route::get('/chat/messages/{conversation}/since/{lastMessageId}', function($conversation, $lastMessageId) {
    $messages = App\Models\Message::where('conversation_id', $conversation)
                ->where('id', '>', $lastMessageId)
                ->orderBy('created_at', 'asc')
                ->get();
    
    return response()->json($messages);
})->middleware('auth')->name('messages.new');