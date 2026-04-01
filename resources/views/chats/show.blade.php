@extends('layouts.app')

@section('title', $otherUser->name)

@php
    $isAiChat = (int) $otherUser->id === (int) config('services.gemini.bot_user_id');
<<<<<<< HEAD
@endphp

@section('content')
<div class="chat-area-wrapper d-flex w-100 h-100">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=40&background=25D366&color=fff' }}"
                 class="avatar me-3"
                 alt="Avatar">

            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold">{{ Auth::user()->name }}</h6>
                <small class="text-muted">
                    @if(Auth::user()->is_online)
                        <span class="text-success">
                            <i class="fas fa-circle me-1" style="font-size: 8px;"></i>En línea
                        </span>
                    @else
                        <span>
                            Últ. vez {{ Auth::user()->last_seen ? \Carbon\Carbon::parse(Auth::user()->last_seen)->diffForHumans() : 'desconocido' }}
                        </span>
                    @endif
                </small>
            </div>

            <a href="{{ route('chats.index') }}" class="btn btn-light btn-sm rounded-circle me-2 d-md-none">
                <i class="fas fa-arrow-left"></i>
            </a>

            <div class="dropdown">
                <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-user me-2"></i>Perfil
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>Salir
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="search-box">
            <div class="position-relative">
                <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                <input type="text" class="search-input ps-5" id="searchUsers" placeholder="Buscar usuario...">
            </div>
            <div id="searchResults" class="mt-2" style="display:none;"></div>
        </div>

        <div class="flex-grow-1 overflow-auto" id="chatsList">
            @foreach($conversations as $conv)
                @php
                    $contact = $conv->user_one == Auth::id() ? $conv->userTwo : $conv->userOne;
                    $lastMsg = $conv->lastMessage;
                    $active = $contact && $contact->id == $otherUser->id ? 'active' : '';
                    $unreadCount = 0;

                    if ($contact) {
                        $unreadCount = $conv->messages()
                            ->where('user_id', $contact->id)
                            ->where('is_read', false)
                            ->count();
                    }
                @endphp

                @if($contact)
                    <a href="{{ route('chat.show', $contact->id) }}"
                       class="text-decoration-none text-dark contact-item d-flex align-items-center {{ $active }}">
                        <div class="position-relative me-3">
                            <img src="{{ $contact->avatar ? asset('storage/' . $contact->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&size=50&background=128C7E&color=fff' }}"
                                 class="avatar"
                                 alt="Avatar"
                                 style="width: 50px; height: 50px;">
                            @if($contact->is_online)
                                <span class="online-indicator"></span>
                            @endif
                        </div>

                        <div class="flex-grow-1 min-width-0">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6 class="mb-0 fw-semibold text-truncate">{{ $contact->name }}</h6>
                                @if($lastMsg)
                                    <small class="text-muted ms-2">{{ $lastMsg->created_at->format('H:i') }}</small>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <small class="text-muted text-truncate" style="max-width: 150px;">
                                    @if($lastMsg)
                                        @if($lastMsg->user_id == Auth::id())
                                            <span class="text-muted">Tú: </span>
                                        @endif

                                        @if($lastMsg->type === 'text')
                                            {{ \Illuminate\Support\Str::limit($lastMsg->message, 20) }}
                                        @elseif($lastMsg->type === 'image')
                                            <i class="fas fa-image me-1"></i>Imagen
                                        @elseif($lastMsg->type === 'video')
                                            <i class="fas fa-video me-1"></i>Video
                                        @endif
                                    @else
                                        Sin mensajes aún
                                    @endif
                                </small>

                                @if($unreadCount > 0)
                                    <span class="badge rounded-pill" style="background-color: var(--whatsapp-green);">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>
    </div>

    <!-- Chat -->
    <div class="chat-area">
        <div class="chat-header">
            <div class="d-flex align-items-center">
                <div class="position-relative me-3">
                    <img src="{{ $otherUser->avatar ? asset('storage/' . $otherUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) . '&size=40&background=128C7E&color=fff' }}"
                         class="avatar"
                         alt="Avatar">
                    @if(!$isAiChat && $otherUser->is_online)
                        <span class="online-indicator"></span>
                    @endif
                </div>

                <div>
                    <h6 class="mb-0 fw-semibold">
                        {{ $otherUser->name }}
                        @if($isAiChat)
                            <span class="badge bg-success ms-2">IA</span>
                        @endif
                    </h6>

                    <small class="text-muted" id="userStatus">
                        @if($isAiChat)
                            <span class="text-success">Asistente inteligente disponible</span>
                        @else
                            @if($otherUser->is_online)
                                <span class="text-success">En línea</span>
                            @else
                                Últ. vez {{ $otherUser->last_seen ? \Carbon\Carbon::parse($otherUser->last_seen)->diffForHumans() : 'desconocido' }}
                            @endif
                        @endif
                    </small>
                </div>
            </div>
        </div>

        <div class="messages-container" id="messagesContainer">
            @foreach($messages as $msg)
                @php
                    $fileUrl = $msg->file_path ? \Illuminate\Support\Facades\Storage::url($msg->file_path) : null;
                @endphp

                <div class="message {{ $msg->user_id == Auth::id() ? 'sent' : 'received' }}" data-message-id="{{ $msg->id }}">
                    <div class="message-content">
                        @if($msg->type === 'text')
                            <div class="chat-text">{!! nl2br(e($msg->message)) !!}</div>
                            <div class="message-time">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($msg->user_id == Auth::id())
                                    <i class="fas fa-check{{ $msg->is_read ? '-double' : '' }} message-status"></i>
                                @endif
                            </div>

                        @elseif($msg->type === 'image' && $fileUrl)
                            <div class="image-preview-container">
                                <img src="{{ $fileUrl }}"
                                     class="img-fluid rounded"
                                     style="max-width: 250px; max-height: 250px; cursor:pointer;"
                                     onclick="openImageModal('{{ $fileUrl }}')"
                                     alt="Imagen">
                                <div class="message-time mt-1">
                                    <span>{{ $msg->created_at->format('H:i') }}</span>
                                    @if($msg->user_id == Auth::id())
                                        <i class="fas fa-check{{ $msg->is_read ? '-double' : '' }} message-status"></i>
                                    @endif
                                </div>
                            </div>

                        @elseif($msg->type === 'video' && $fileUrl)
                            <div class="video-preview-container">
                                <video controls class="rounded" style="max-width: 300px; max-height: 300px;">
                                    <source src="{{ $fileUrl }}" type="video/mp4">
                                    Tu navegador no soporta el elemento de video.
                                </video>
                                <div class="message-time mt-1">
                                    <span>{{ $msg->created_at->format('H:i') }}</span>
                                    @if($msg->user_id == Auth::id())
                                        <i class="fas fa-check{{ $msg->is_read ? '-double' : '' }} message-status"></i>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div id="aiTypingIndicator" style="display:none; padding: 0 15px 10px 15px;">
            <div class="message received">
                <div class="message-content">
                    <div class="chat-text text-muted menssage-chat">
                        <i class="fas fa-robot me-2"></i>La IA está escribiendo...
                    </div>
                </div>
            </div>
        </div>

        <div class="message-input-area">
            <button type="button" class="btn-icon" id="attachFileBtn" title="Adjuntar archivo">
                <i class="fas fa-plus-circle"></i>
            </button>

            <input type="file" id="fileInput" style="display:none;" accept="image/*,video/*">

            <input type="text"
                   class="message-input"
                   id="messageInput"
                   placeholder="{{ $isAiChat ? 'Escribe tu mensaje para la IA...' : 'Escribe un mensaje...' }}"
                   autocomplete="off">

            <button type="button" class="btn-icon btn-send" id="sendMessageBtn" disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
=======
    $authAvatarUrl = Auth::user()->avatar
        ? asset('storage/' . ltrim(Auth::user()->avatar, '/'))
        : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=40&background=25D366&color=fff';

    $otherUserAvatarUrl = $otherUser->avatar
        ? asset('storage/' . ltrim($otherUser->avatar, '/'))
        : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) . '&size=40&background=128C7E&color=fff';

    $lastInitialMessage = $messages->last();
    $lastInitialMessageId = is_array($lastInitialMessage) ? ($lastInitialMessage['id'] ?? 0) : 0;
@endphp

@section('content')
<div class="container-fluid px-0">
    <div class="chat-layout chat-show-layout">
        <!-- Sidebar desktop -->
        <aside class="sidebar chat-sidebar d-none d-md-flex flex-column">
            <div class="sidebar-header">
                <img src="{{ $authAvatarUrl }}"
                    class="avatar me-3"
                    alt="Avatar">

                <div class="flex-grow-1 min-w-0">
                    <h6 class="mb-0 fw-semibold text-truncate">{{ Auth::user()->name }}</h6>
                    <small class="text-muted">
                        @if(Auth::user()->is_online)
                            <span class="text-success">
                                <i class="fas fa-circle me-1" style="font-size: 8px;"></i>En línea
                            </span>
                        @else
                            <span>
                                Últ. vez {{ Auth::user()->last_seen ? \Carbon\Carbon::parse(Auth::user()->last_seen)->diffForHumans() : 'desconocido' }}
                            </span>
                        @endif
                    </small>
                </div>

                <div class="dropdown">
                    <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-ellipsis-v"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                            <a class="dropdown-item align-items-center" href="{{ route('profile.edit') }}" style="display:flex; gap: 10px;">
                                <i class="fas fa-user me-2"></i>Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item align-items-center" href="{{ route('settingfaces.settingfaceprofile') }}" style="display:flex; gap: 10px;">
                                <img src="{{ asset('image/icons/ar_on_you.svg') }}" alt="settingFaces" width="20" class="black-filter">Acceso Biometricos
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item align-items-center" href="{{ route('biometric.settings') }}" style="display:flex; gap: 10px;">
                                <img src="{{ asset('image/icons/fingerprint.svg') }}" alt="settingfingerprints" width="20" class="black-filter">Configuración huella
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item align-items-center text-danger" style="display:flex; gap: 10px;">
                                    <i class="fas fa-sign-out-alt me-2"></i>Salir
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="search-box">
                <div class="position-relative">
                    <i class="fas fa-search position-absolute text-muted search-icon"></i>
                    <input type="text" class="search-input ps-5" id="searchUsers" placeholder="Buscar usuario...">
                </div>

                <div class="mt-2">
                    <button
                        type="button"
                        class="btn btn-chat w-100 rounded-pill d-flex align-items-center justify-content-center gap-2"
                        data-bs-toggle="modal"
                        data-bs-target="#createGroupModal">
                        <i class="fas fa-users me-2"></i>Crear Grupo
                    </button>
                </div>

                <div class="mt-2">
                    <a href="{{ route('chat.show', config('services.gemini.bot_user_id')) }}"
                        class="btn btn-chat w-100 rounded-pill d-flex align-items-center justify-content-center gap-2">
                        <img src="{{ asset('image/robot.png') }}" alt="Robot" width="32">
                        <span>Habla con una asistente</span>
                    </a>
                </div>

                <div id="searchResults" class="search-results" style="display:none;"></div>
            </div>

            <div class="chat-list flex-grow-1 overflow-auto" id="chatsList">
                <div class="px-3 pt-3 pb-2 small text-uppercase fw-bold text-muted">
                    Grupos
                </div>

                @forelse($groups ?? [] as $groupItem)
                    @php
                        $lastGroupMsg = $groupItem->lastMessage;
                        $groupAvatarUrl = $groupItem->image
                            ? asset('storage/' . ltrim($groupItem->image, '/'))
                            : 'https://ui-avatars.com/api/?name=' . urlencode($groupItem->name) . '&size=50&background=6c757d&color=fff';
                    @endphp

                    <a href="{{ route('groups.show', $groupItem->id) }}"
                        class="text-decoration-none text-dark contact-item d-flex align-items-center">
                        <div class="position-relative me-3 flex-shrink-0">
                            <img src="{{ $groupAvatarUrl }}"
                                class="avatar"
                                alt="Grupo"
                                style="width: 50px; height: 50px;">
                        </div>

                        <div class="flex-grow-1 min-w-0">
                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <h6 class="mb-0 fw-semibold text-truncate">{{ $groupItem->name }}</h6>
                                @if($lastGroupMsg)
                                    <small class="text-muted flex-shrink-0">{{ $lastGroupMsg->created_at->format('H:i') }}</small>
                                @endif
                            </div>

                            <div class="d-flex justify-content-between align-items-center gap-2">
                                <small class="text-muted text-truncate chat-preview-text">
                                    @if($lastGroupMsg)
                                        @if($lastGroupMsg->type === 'text')
                                            {{ \Illuminate\Support\Str::limit(($lastGroupMsg->user->name ?? 'Usuario') . ': ' . ($lastGroupMsg->message ?? ''), 30) }}
                                        @elseif($lastGroupMsg->type === 'image')
                                            <i class="fas fa-image me-1"></i>{{ $lastGroupMsg->user->name ?? 'Usuario' }}: Imagen
                                        @elseif($lastGroupMsg->type === 'video')
                                            <i class="fas fa-video me-1"></i>{{ $lastGroupMsg->user->name ?? 'Usuario' }}: Video
                                        @endif
                                    @else
                                        <span class="text-muted">{{ $groupItem->users_count ?? ($groupItem->users->count() ?? 0) }} miembros</span>
                                    @endif
                                </small>
                            </div>
                        </div>
                    </a>
                @empty
                    <div class="px-3 pb-2 text-muted small">
                        No perteneces a grupos todavía
                    </div>
                @endforelse

                <div class="px-3 pt-3 pb-2 small text-uppercase fw-bold text-muted">
                    Chats privados
                </div>

                @foreach($conversations as $conv)
                    @php
                        $contact = $conv->user_one == Auth::id() ? $conv->userTwo : $conv->userOne;
                        $lastMsg = $conv->lastMessage;
                        $active = $contact && $contact->id == $otherUser->id ? 'active' : '';
                        $unreadCount = 0;

                        if ($contact) {
                            $unreadCount = $conv->messages()
                                ->where('user_id', $contact->id)
                                ->where('is_read', false)
                                ->count();
                        }

                        $contactAvatarUrl = $contact && $contact->avatar
                            ? asset('storage/' . ltrim($contact->avatar, '/'))
                            : ($contact
                                ? 'https://ui-avatars.com/api/?name=' . urlencode($contact->name) . '&size=50&background=128C7E&color=fff'
                                : null);
                    @endphp

                    @if($contact)
                        <a href="{{ route('chat.show', $contact->id) }}"
                            class="text-decoration-none text-dark contact-item d-flex align-items-center {{ $active }}">
                            <div class="position-relative me-3 flex-shrink-0">
                                <img src="{{ $contactAvatarUrl }}"
                                    class="avatar"
                                    alt="Avatar"
                                    style="width: 50px; height: 50px;">
                                @if($contact->is_online)
                                    <span class="online-indicator"></span>
                                @endif
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <h6 class="mb-0 fw-semibold text-truncate">{{ $contact->name }}</h6>
                                    @if($lastMsg)
                                        <small class="text-muted flex-shrink-0">{{ $lastMsg->created_at->format('H:i') }}</small>
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <small class="text-muted text-truncate chat-preview-text">
                                        @if($lastMsg)
                                            @if($lastMsg->user_id == Auth::id())
                                                <span class="text-muted">Tú: </span>
                                            @endif

                                            @if($lastMsg->type === 'text')
                                                {{ \Illuminate\Support\Str::limit($lastMsg->message, 20) }}
                                            @elseif($lastMsg->type === 'image')
                                                <i class="fas fa-image me-1"></i>Imagen
                                            @elseif($lastMsg->type === 'video')
                                                <i class="fas fa-video me-1"></i>Video
                                            @endif
                                        @else
                                            Sin mensajes aún
                                        @endif
                                    </small>

                                    @if($unreadCount > 0)
                                        <span class="badge rounded-pill flex-shrink-0"
                                            style="background-color: var(--whatsapp-green);">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </div>
        </aside>

        <!-- Chat -->
        <section class="chat-area chat-main">
            <div class="chat-header">
                <div class="d-flex align-items-center min-w-0 w-100">
                    <a href="{{ route('chats.index') }}" class="btn btn-light btn-sm rounded-circle me-2 d-md-none flex-shrink-0">
                        <i class="fas fa-arrow-left"></i>
                    </a>

                    <div class="position-relative me-3 flex-shrink-0">
                        <img src="{{ $otherUserAvatarUrl }}"
                            class="avatar"
                            alt="Avatar">
                        @if(!$isAiChat && $otherUser->is_online)
                            <span class="online-indicator"></span>
                        @endif
                    </div>

                    <div class="min-w-0">
                        <h6 class="mb-0 fw-semibold text-truncate">
                            {{ $otherUser->name }}
                            @if($isAiChat)
                                <span class="badge bg-success ms-2">IA</span>
                            @endif
                        </h6>

                        <small class="text-muted d-block text-truncate" id="userStatus">
                            @if($isAiChat)
                                <span class="text-success">Asistente inteligente disponible</span>
                            @else
                                @if($otherUser->is_online)
                                    <span class="text-success">En línea</span>
                                @else
                                    Últ. vez {{ $otherUser->last_seen ? \Carbon\Carbon::parse($otherUser->last_seen)->diffForHumans() : 'desconocido' }}
                                @endif
                            @endif
                        </small>
                    </div>
                </div>
            </div>

            <div class="messages-container" id="messagesContainer">
                @foreach($messages as $msg)
                    @php
                        $fileUrl = $msg['file_url'] ?? null;
                        $isMine = (int) ($msg['user_id'] ?? 0) === (int) Auth::id();
                        $time = !empty($msg['created_at'])
                            ? \Carbon\Carbon::parse($msg['created_at'])->format('H:i')
                            : '';
                    @endphp

                    <div class="message {{ $isMine ? 'sent' : 'received' }}" data-message-id="{{ $msg['id'] }}">
                        <div class="message-content">
                            @if(($msg['type'] ?? 'text') === 'text')
                                <div class="chat-text">{!! nl2br(e($msg['message'] ?? '')) !!}</div>
                                <div class="message-time">
                                    <span>{{ $time }}</span>
                                    @if($isMine)
                                        <i class="fas fa-check{{ !empty($msg['is_read']) ? '-double' : '' }} message-status"></i>
                                    @endif
                                </div>

                            @elseif(($msg['type'] ?? '') === 'image' && $fileUrl)
                                <div class="image-preview-container">
                                    <img src="{{ $fileUrl }}"
                                        class="img-fluid rounded chat-media-image"
                                        onclick="openImageModal('{{ $fileUrl }}')"
                                        alt="Imagen">

                                    <div class="media-actions mt-2">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-light">Abrir</a>
                                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-light">Descargar</a>
                                        <button type="button" class="btn btn-sm btn-light" onclick="shareMedia('{{ $fileUrl }}', 'Imagen')">Compartir</button>
                                        <button type="button" class="btn btn-sm btn-light" onclick="copyMediaLink('{{ $fileUrl }}')">Copiar enlace</button>
                                    </div>

                                    <div class="message-time mt-1">
                                        <span>{{ $time }}</span>
                                        @if($isMine)
                                            <i class="fas fa-check{{ !empty($msg['is_read']) ? '-double' : '' }} message-status"></i>
                                        @endif
                                    </div>
                                </div>

                            @elseif(($msg['type'] ?? '') === 'video' && $fileUrl)
                                <div class="video-preview-container">
                                    <video controls class="rounded chat-media-video">
                                        <source src="{{ $fileUrl }}" type="video/mp4">
                                        Tu navegador no soporta el elemento de video.
                                    </video>

                                    <div class="media-actions mt-2">
                                        <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-light">Abrir</a>
                                        <a href="{{ $fileUrl }}" download class="btn btn-sm btn-light">Descargar</a>
                                        <button type="button" class="btn btn-sm btn-light" onclick="shareMedia('{{ $fileUrl }}', 'Video')">Compartir</button>
                                        <button type="button" class="btn btn-sm btn-light" onclick="copyMediaLink('{{ $fileUrl }}')">Copiar enlace</button>
                                    </div>

                                    <div class="message-time mt-1">
                                        <span>{{ $time }}</span>
                                        @if($isMine)
                                            <i class="fas fa-check{{ !empty($msg['is_read']) ? '-double' : '' }} message-status"></i>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div id="aiTypingIndicator" style="display:none; padding: 0 15px 10px 15px;">
                <div class="message received">
                    <div class="message-content">
                        <div class="chat-text text-muted menssage-chat">
                            <i class="fas fa-robot me-2"></i>La IA está escribiendo...
                        </div>
                    </div>
                </div>
            </div>

            <div class="message-input-area">
                <button type="button" class="btn-icon" id="attachFileBtn" title="Adjuntar archivo">
                    <i class="fas fa-plus-circle"></i>
                </button>

                <input type="file" id="fileInput" style="display:none;" accept="image/*,video/*">

                <input type="text"
                    class="message-input"
                    id="messageInput"
                    placeholder="{{ $isAiChat ? 'Escribe tu mensaje para la IA...' : 'Escribe un mensaje...' }}"
                    autocomplete="off">

                <button type="button" class="btn-icon btn-send" id="sendMessageBtn" disabled>
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </section>
>>>>>>> main
    </div>
</div>

<input type="hidden" id="conversationId" value="{{ $conversation->id }}">
<input type="hidden" id="otherUserId" value="{{ $otherUser->id }}">
<input type="hidden" id="isAiChat" value="{{ $isAiChat ? 1 : 0 }}">
<<<<<<< HEAD
=======
<input type="hidden" id="authUserId" value="{{ Auth::id() }}">
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
<input type="hidden" id="lastMessageIdValue" value="{{ $lastInitialMessageId }}">
<input type="hidden" id="searchUsersUrl" value="{{ route('chat.search') }}">
<input type="hidden" id="sendMessageUrl" value="{{ route('messages.send') }}">
<input type="hidden" id="sendFileUrl" value="{{ route('messages.send-file') }}">
<input type="hidden" id="markReadUrl" value="{{ route('messages.mark-read') }}">
<input type="hidden" id="groupsStoreUrl" value="{{ route('groups.store') }}">
>>>>>>> main

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0 position-relative">
                <button type="button"
<<<<<<< HEAD
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
=======
                    class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                    data-bs-dismiss="modal"></button>
>>>>>>> main
                <img src="" id="modalImage" class="img-fluid w-100" alt="Imagen">
            </div>
        </div>
    </div>
</div>
<<<<<<< HEAD
@endsection

@push('styles')
<style>
.message-content {
    max-width: 65%;
    padding: 8px 12px;
    border-radius: 7.5px;
    position: relative;
    word-wrap: break-word;
    overflow-wrap: anywhere;
    white-space: normal;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.message-content .chat-text,
.message-content p {
    margin: 0;
    white-space: pre-wrap;
    overflow-wrap: anywhere;
    word-break: break-word;
    line-height: 1.45;
}

.message.sent .message-content {
    background-color: #d9fdd3;
    border-top-right-radius: 0;
}

.message.received .message-content {
    background-color: #fff;
    border-top-left-radius: 0;
}
</style>
@endpush

@push('scripts')
<script>
$(document).ready(function () {
    const conversationId = $('#conversationId').val();
    const currentUserId = {{ Auth::id() }};
    const isAiChat = $('#isAiChat').val() === '1';
    const messagesContainer = $('#messagesContainer');
    const messageInput = $('#messageInput');
    const sendBtn = $('#sendMessageBtn');
    const attachBtn = $('#attachFileBtn');
    const fileInput = $('#fileInput');
    let lastMessageId = {{ $messages->last()->id ?? 0 }};
    let polling = null;

    setupAjaxCsrf();
    scrollToBottom();
    startPolling();

    messageInput.on('input', function () {
        sendBtn.prop('disabled', $(this).val().trim() === '');
    });

    messageInput.on('keypress', function (e) {
        if (e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            if (!sendBtn.prop('disabled')) {
                sendMessage();
            }
        }
    });

    sendBtn.on('click', sendMessage);

    attachBtn.on('click', function () {
        fileInput.click();
    });

    fileInput.on('change', sendFile);

    let searchTimeout;
    $('#searchUsers').on('keyup', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }

        $('#searchResults')
            .html('<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>')
            .show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("chat.search") }}',
                method: 'GET',
                data: { q: query },
                success: function (users) {
                    if (users && users.length > 0) {
                        let html = '<div class="list-group mt-2" style="max-height:300px; overflow-y:auto;">';

                        users.forEach(user => {
                            const avatar = user.avatar
                                ? '/storage/' + user.avatar
                                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                            html += `
                                <a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
                                    <img src="${avatar}" class="rounded-circle me-3" width="40" height="40">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold">${escapeHtml(user.name)}</h6>
                                            ${user.is_online ? '<span class="badge bg-success">En línea</span>' : ''}
                                        </div>
                                        <small class="text-muted">${escapeHtml(user.email ?? '')}</small>
                                    </div>
                                </a>
                            `;
                        });

                        html += '</div>';
                        $('#searchResults').html(html).show();
                    } else {
                        $('#searchResults').html('<div class="alert alert-info mt-2">No se encontraron usuarios</div>').show();
                    }
                },
                error: function () {
                    $('#searchResults').html('<div class="alert alert-danger mt-2">Error en la búsqueda</div>').show();
                }
            });
        }, 400);
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#searchUsers, #searchResults').length) {
            $('#searchResults').hide();
        }
    });

    function setupAjaxCsrf() {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });
    }

    function sendMessage() {
        const message = messageInput.val().trim();
        if (!message) return;

        const originalHtml = sendBtn.html();
        sendBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
        attachBtn.prop('disabled', true);

        if (isAiChat) {
            showAiTyping();
        }

        $.ajax({
            url: '{{ route("messages.send") }}',
            method: 'POST',
            data: {
                conversation_id: conversationId,
                message: message
            },
            success: function (response) {
                messageInput.val('');
                sendBtn.html('<i class="fas fa-paper-plane"></i>').prop('disabled', true);
                attachBtn.prop('disabled', false);
                hideAiTyping();

                if (response.success) {
                    if (response.data) {
                        appendMessage(response.data, 'sent');
                        lastMessageId = Math.max(lastMessageId, parseInt(response.data.id || 0));
                    }

                    if (response.ai_message) {
                        appendMessage(response.ai_message, 'received');
                        lastMessageId = Math.max(lastMessageId, parseInt(response.ai_message.id || 0));
                    }

                    if (response.ai_error) {
                        showToast('La IA no pudo responder en este momento', 'warning');
                    }
                } else {
                    showToast(response.message || 'No se pudo enviar el mensaje', 'error');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                sendBtn.html(originalHtml).prop('disabled', false);
                attachBtn.prop('disabled', false);
                hideAiTyping();
                showToast('Error al enviar mensaje', 'error');
            }
        });
    }

    function sendFile(e) {
        const file = e.target.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('conversation_id', conversationId);

        const originalHtml = attachBtn.html();
        attachBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
        sendBtn.prop('disabled', true);

        $.ajax({
            url: '{{ route("messages.send-file") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                attachBtn.html(originalHtml).prop('disabled', false);
                fileInput.val('');

                if (response.success && response.data) {
                    const fileMessage = response.data;

                    if (response.file_url) {
                        fileMessage.file_url = response.file_url;
                    }

                    appendMessage(fileMessage, 'sent');
                    lastMessageId = Math.max(lastMessageId, parseInt(fileMessage.id || 0));
                    showToast('Archivo enviado', 'success');
                } else {
                    showToast(response.message || 'No se pudo enviar el archivo', 'error');
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
                attachBtn.html(originalHtml).prop('disabled', false);
                fileInput.val('');
                showToast('Error al enviar archivo', 'error');
            }
        });
    }

    function startPolling() {
        polling = setInterval(checkNewMessages, 2000);
    }

    function checkNewMessages() {
        $.ajax({
            url: `/chat/messages/${conversationId}/since/${lastMessageId}`,
            method: 'GET',
            success: function (messages) {
                if (!messages || !messages.length) return;

                messages.forEach(msg => {
                    if ($(`.message[data-message-id="${msg.id}"]`).length) {
                        return;
                    }

                    const type = parseInt(msg.user_id) === currentUserId ? 'sent' : 'received';

                    if ((msg.type === 'image' || msg.type === 'video') && msg.file_path && !msg.file_url) {
                        msg.file_url = '/storage/' + msg.file_path;
                    }

                    appendMessage(msg, type);
                    lastMessageId = Math.max(lastMessageId, parseInt(msg.id || 0));
                });

                $.post('{{ route("messages.mark-read") }}', {
                    conversation_id: conversationId
                });
            },
            error: function (xhr) {
                console.error('Polling error:', xhr.responseText);
            }
        });
    }

    function appendMessage(message, type) {
        if (!message || !message.id) return;

        if ($(`.message[data-message-id="${message.id}"]`).length) {
            return;
        }

        const html = `
=======

<div class="modal fade" id="createGroupModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Grupo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="groupName" class="form-label">Nombre del grupo</label>
                    <input type="text" id="groupName" class="form-control" placeholder="Ej. Equipo de trabajo">
                </div>

                <div class="mb-3">
                    <label for="groupDescription" class="form-label">Descripción (opcional)</label>
                    <input type="text" id="groupDescription" class="form-control" placeholder="Descripción breve">
                </div>

                <div class="mb-2">
                    <label for="groupUserSearch" class="form-label">Buscar usuarios</label>
                    <input type="text" id="groupUserSearch" class="form-control" placeholder="Escribe nombre o correo">
                </div>

                <div id="groupUserResults" class="list-group mt-2"
                    style="max-height: 220px; overflow-y: auto; display:none;"></div>

                <div class="mt-3">
                    <label class="form-label">Miembros seleccionados</label>
                    <div id="selectedGroupUsers" class="d-flex flex-wrap gap-2"></div>
                    <small class="text-muted d-block mt-2">Tú se agregarás automáticamente como administrador.</small>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-chat" id="createGroupBtn">Crear</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        const conversationId = $('#conversationId').val();
        const currentUserId = Number($('#authUserId').val() || 0);
        const isAiChat = $('#isAiChat').val() === '1';
        const csrfToken = $('#csrfToken').val();
        const searchUsersUrl = $('#searchUsersUrl').val();
        const sendMessageUrl = $('#sendMessageUrl').val();
        const sendFileUrl = $('#sendFileUrl').val();
        const markReadUrl = $('#markReadUrl').val();
        const groupsStoreUrl = $('#groupsStoreUrl').val();

        const messagesContainer = $('#messagesContainer');
        const messageInput = $('#messageInput');
        const sendBtn = $('#sendMessageBtn');
        const attachBtn = $('#attachFileBtn');
        const fileInput = $('#fileInput');

        let lastMessageId = Number($('#lastMessageIdValue').val() || 0);
        let polling = null;
        let searchTimeout;
        let groupSearchTimeout;

        const selectedUsers = new Map();
        const authUserId = Number($('#authUserId').val() || 0);

        setupAjaxCsrf();
        scrollToBottom();
        startPolling();
        renderSelectedGroupUsers();

        messageInput.on('input', function() {
            sendBtn.prop('disabled', $(this).val().trim() === '');
        });

        messageInput.on('keypress', function(e) {
            if (e.which === 13 && !e.shiftKey) {
                e.preventDefault();
                if (!sendBtn.prop('disabled')) {
                    sendMessage();
                }
            }
        });

        sendBtn.on('click', sendMessage);

        attachBtn.on('click', function() {
            fileInput.click();
        });

        fileInput.on('change', sendFile);

        $('#searchUsers').on('keyup', function() {
            clearTimeout(searchTimeout);
            const query = $(this).val().trim();

            if (query.length < 2) {
                $('#searchResults').hide().empty();
                return;
            }

            $('#searchResults')
                .html('<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>')
                .show();

            searchTimeout = setTimeout(() => {
                $.ajax({
                    url: searchUsersUrl,
                    method: 'GET',
                    data: { q: query },
                    success: function(users) {
                        if (users && users.length > 0) {
                            let html = '<div class="list-group">';

                            users.forEach(user => {
                                const avatar = user.avatar_url ||
                                    'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                                html += `
                                <a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
                                    <img src="${avatar}" class="rounded-circle me-3 flex-shrink-0" width="40" height="40" alt="Avatar">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center gap-2">
                                            <h6 class="mb-0 fw-semibold text-truncate">${escapeHtml(user.name)}</h6>
                                            ${user.is_online ? '<span class="badge bg-success flex-shrink-0">En línea</span>' : ''}
                                        </div>
                                        <small class="text-muted text-truncate d-block">${escapeHtml(user.email ?? '')}</small>
                                    </div>
                                </a>
                            `;
                            });

                            html += '</div>';
                            $('#searchResults').html(html).show();
                        } else {
                            $('#searchResults').html('<div class="alert alert-info mt-2 mb-0">No se encontraron usuarios</div>').show();
                        }
                    },
                    error: function() {
                        $('#searchResults').html('<div class="alert alert-danger mt-2 mb-0">Error en la búsqueda</div>').show();
                    }
                });
            }, 400);
        });

        $('#groupUserSearch').on('keyup', function() {
            clearTimeout(groupSearchTimeout);
            const query = $(this).val().trim();

            if (query.length < 2) {
                $('#groupUserResults').hide().empty();
                return;
            }

            $('#groupUserResults')
                .html('<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>')
                .show();

            groupSearchTimeout = setTimeout(() => {
                $.ajax({
                    url: searchUsersUrl,
                    method: 'GET',
                    data: { q: query },
                    success: function(users) {
                        const filteredUsers = (users || []).filter(user =>
                            parseInt(user.id) !== authUserId && !selectedUsers.has(parseInt(user.id))
                        );

                        if (!filteredUsers.length) {
                            $('#groupUserResults').html('<div class="alert alert-info mb-0">No hay usuarios disponibles</div>').show();
                            return;
                        }

                        let html = '';

                        filteredUsers.forEach(user => {
                            const avatar = user.avatar_url ||
                                'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                            html += `
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center add-group-user"
                                    data-user-id="${user.id}"
                                    data-user-name="${escapeHtml(user.name)}"
                                    data-user-email="${escapeHtml(user.email ?? '')}">
                                <img src="${avatar}" class="rounded-circle me-3 flex-shrink-0" width="40" height="40" alt="Avatar">
                                <div class="flex-grow-1 min-w-0 text-start">
                                    <div class="fw-semibold text-truncate">${escapeHtml(user.name)}</div>
                                    <small class="text-muted text-truncate d-block">${escapeHtml(user.email ?? '')}</small>
                                </div>
                                <i class="fas fa-plus text-success"></i>
                            </button>
                        `;
                        });

                        $('#groupUserResults').html(html).show();
                    },
                    error: function() {
                        $('#groupUserResults').html('<div class="alert alert-danger mb-0">Error al buscar usuarios</div>').show();
                    }
                });
            }, 350);
        });

        $(document).on('click', '.add-group-user', function() {
            const userId = parseInt($(this).data('user-id'));
            const userName = $(this).data('user-name');
            const userEmail = $(this).data('user-email');

            if (!selectedUsers.has(userId)) {
                selectedUsers.set(userId, {
                    id: userId,
                    name: userName,
                    email: userEmail
                });
            }

            renderSelectedGroupUsers();
            $('#groupUserSearch').trigger('keyup');
        });

        $(document).on('click', '.remove-group-user', function() {
            const userId = parseInt($(this).data('user-id'));
            selectedUsers.delete(userId);
            renderSelectedGroupUsers();
            $('#groupUserSearch').trigger('keyup');
        });

        $('#createGroupBtn').on('click', function() {
            createGroup();
        });

        $('#createGroupModal').on('hidden.bs.modal', function() {
            resetGroupModal();
        });

        $(document).click(function(e) {
            if (!$(e.target).closest('#searchUsers, #searchResults').length) {
                $('#searchResults').hide();
            }
        });

        $(window).on('beforeunload', function() {
            if (polling) {
                clearInterval(polling);
            }
        });

        function setupAjaxCsrf() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
        }

        function sendMessage() {
            const message = messageInput.val().trim();
            if (!message) return;

            const originalHtml = sendBtn.html();
            sendBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');
            attachBtn.prop('disabled', true);

            if (isAiChat) {
                showAiTyping();
            }

            $.ajax({
                url: sendMessageUrl,
                method: 'POST',
                data: {
                    conversation_id: conversationId,
                    message: message
                },
                success: function(response) {
                    messageInput.val('');
                    sendBtn.html('<i class="fas fa-paper-plane"></i>').prop('disabled', true);
                    attachBtn.prop('disabled', false);
                    hideAiTyping();

                    if (response.success) {
                        if (response.data) {
                            appendMessage(response.data, 'sent');
                            lastMessageId = Math.max(lastMessageId, parseInt(response.data.id || 0));
                        }

                        if (response.ai_message) {
                            appendMessage(response.ai_message, 'received');
                            lastMessageId = Math.max(lastMessageId, parseInt(response.ai_message.id || 0));
                        }

                        if (response.ai_error) {
                            showToast('La IA no pudo responder en este momento', 'warning');
                        }
                    } else {
                        showToast(response.message || 'No se pudo enviar el mensaje', 'error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    sendBtn.html(originalHtml).prop('disabled', false);
                    attachBtn.prop('disabled', false);
                    hideAiTyping();
                    showToast('Error al enviar mensaje', 'error');
                }
            });
        }

        function sendFile(e) {
            const file = e.target.files[0];
            if (!file) return;

            const formData = new FormData();
            formData.append('file', file);
            formData.append('conversation_id', conversationId);

            const originalHtml = attachBtn.html();
            attachBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);
            sendBtn.prop('disabled', true);

            $.ajax({
                url: sendFileUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    attachBtn.html(originalHtml).prop('disabled', false);
                    fileInput.val('');

                    if (response.success && response.data) {
                        const fileMessage = response.data;
                        appendMessage(fileMessage, 'sent');
                        lastMessageId = Math.max(lastMessageId, parseInt(fileMessage.id || 0));
                        showToast('Archivo enviado', 'success');
                    } else {
                        showToast(response.message || 'No se pudo enviar el archivo', 'error');
                    }
                },
                error: function(xhr) {
                    console.error(xhr.responseText);
                    attachBtn.html(originalHtml).prop('disabled', false);
                    fileInput.val('');
                    showToast('Error al enviar archivo', 'error');
                }
            });
        }

        function startPolling() {
            polling = setInterval(checkNewMessages, 2000);
        }

        function checkNewMessages() {
            $.ajax({
                url: `/chat/messages/${conversationId}/since/${lastMessageId}`,
                method: 'GET',
                success: function(messages) {
                    if (!messages || !messages.length) return;

                    messages.forEach(msg => {
                        if ($(`.message[data-message-id="${msg.id}"]`).length) {
                            return;
                        }

                        const type = parseInt(msg.user_id) === currentUserId ? 'sent' : 'received';
                        appendMessage(msg, type);
                        lastMessageId = Math.max(lastMessageId, parseInt(msg.id || 0));
                    });

                    $.post(markReadUrl, {
                        conversation_id: conversationId
                    });
                },
                error: function(xhr) {
                    console.error('Polling error:', xhr.responseText);
                }
            });
        }

        function createGroup() {
            const name = $('#groupName').val().trim();
            const description = $('#groupDescription').val().trim();
            const userIds = Array.from(selectedUsers.keys());

            if (!name) {
                showToast('Debes escribir un nombre para el grupo', 'warning');
                return;
            }

            if (userIds.length === 0) {
                showToast('Debes seleccionar al menos un usuario', 'warning');
                return;
            }

            if (!groupsStoreUrl) {
                showToast('La ruta groups.store está vacía', 'error');
                return;
            }

            $('#createGroupBtn').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando');

            fetch(groupsStoreUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    name: name,
                    description: description,
                    user_ids: userIds
                })
            })
            .then(async response => {
                const data = await response.json();

                if (!response.ok) {
                    throw data;
                }

                return data;
            })
            .then(data => {
                if (data.success && data.redirect) {
                    window.location.href = data.redirect;
                    return;
                }

                showToast(data.message || 'No se pudo crear el grupo', 'error');
                $('#createGroupBtn').prop('disabled', false).html('Crear');
            })
            .catch(error => {
                console.error(error);
                showToast(error.message || 'Error al crear el grupo', 'error');
                $('#createGroupBtn').prop('disabled', false).html('Crear');
            });
        }

        function renderSelectedGroupUsers() {
            let html = `
            <span class="badge bg-success d-inline-flex align-items-center px-3 py-2">
                Tú (admin)
            </span>
        `;

            selectedUsers.forEach(user => {
                html += `
                <span class="badge bg-secondary d-inline-flex align-items-center px-3 py-2">
                    ${escapeHtml(user.name)}
                    <button type="button"
                            class="btn-close btn-close-white ms-2 remove-group-user"
                            data-user-id="${user.id}"
                            style="font-size:10px;"></button>
                </span>
            `;
            });

            $('#selectedGroupUsers').html(html);
        }

        function resetGroupModal() {
            $('#groupName').val('');
            $('#groupDescription').val('');
            $('#groupUserSearch').val('');
            $('#groupUserResults').hide().empty();
            selectedUsers.clear();
            renderSelectedGroupUsers();
            $('#createGroupBtn').prop('disabled', false).html('Crear');
        }

        function appendMessage(message, type) {
            if (!message || !message.id) return;

            if ($(`.message[data-message-id="${message.id}"]`).length) {
                return;
            }

            const html = `
>>>>>>> main
            <div class="message ${type}" data-message-id="${message.id}">
                <div class="message-content">
                    ${renderMessageContent(message, type)}
                </div>
            </div>
        `;

<<<<<<< HEAD
        messagesContainer.append(html);
        scrollToBottom();
    }

    function renderMessageContent(message, type) {
        const time = formatTime(message.created_at);
        const statusIcon = type === 'sent'
            ? `<i class="fas fa-check${message.is_read ? '-double' : ''} message-status"></i>`
            : '';

        if (message.type === 'text') {
            return `
=======
            messagesContainer.append(html);
            scrollToBottom();
        }

        function renderMessageContent(message, type) {
            const time = formatTime(message.created_at);
            const statusIcon = type === 'sent'
                ? `<i class="fas fa-check${message.is_read ? '-double' : ''} message-status"></i>`
                : '';

            if (message.type === 'text') {
                return `
>>>>>>> main
                <div class="chat-text">${nl2br(escapeHtml(message.message || ''))}</div>
                <div class="message-time">
                    <span>${time}</span>
                    ${statusIcon}
                </div>
            `;
<<<<<<< HEAD
        }

        if (message.type === 'image') {
            const imageUrl = message.file_url || (message.file_path ? '/storage/' + message.file_path : '');
            return `
                <div class="image-preview-container">
                    <img src="${imageUrl}"
                         class="img-fluid rounded"
                         style="max-width: 250px; max-height: 250px; cursor:pointer;"
                         onclick="openImageModal('${imageUrl}')"
                         alt="Imagen">
                    <div class="message-time mt-1">
                        <span>${time}</span>
                        ${statusIcon}
                    </div>
                </div>
            `;
        }

        if (message.type === 'video') {
            const videoUrl = message.file_url || (message.file_path ? '/storage/' + message.file_path : '');
            return `
                <div class="video-preview-container">
                    <video controls class="rounded" style="max-width: 300px; max-height: 300px;">
                        <source src="${videoUrl}" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                    <div class="message-time mt-1">
                        <span>${time}</span>
                        ${statusIcon}
                    </div>
                </div>
            `;
        }

        return `
=======
            }

            if (message.type === 'image') {
                const imageUrl = message.file_url || '';
                return `
        <div class="image-preview-container">
            <img src="${imageUrl}"
                 class="img-fluid rounded chat-media-image"
                 onclick="openImageModal('${imageUrl}')"
                 alt="Imagen">

            <div class="media-actions mt-2">
                <a href="${imageUrl}" target="_blank" class="btn btn-sm btn-light">Abrir</a>
                <a href="${imageUrl}" download class="btn btn-sm btn-light">Descargar</a>
                <button type="button" class="btn btn-sm btn-light" onclick="shareMedia('${imageUrl}', 'Imagen')">Compartir</button>
                <button type="button" class="btn btn-sm btn-light" onclick="copyMediaLink('${imageUrl}')">Copiar enlace</button>
            </div>

            <div class="message-time mt-1">
                <span>${time}</span>
                ${statusIcon}
            </div>
        </div>
    `;
            }

            if (message.type === 'video') {
                const videoUrl = message.file_url || '';
                return `
        <div class="video-preview-container">
            <video controls class="rounded chat-media-video">
                <source src="${videoUrl}" type="video/mp4">
                Tu navegador no soporta el elemento de video.
            </video>

            <div class="media-actions mt-2">
                <a href="${videoUrl}" target="_blank" class="btn btn-sm btn-light">Abrir</a>
                <a href="${videoUrl}" download class="btn btn-sm btn-light">Descargar</a>
                <button type="button" class="btn btn-sm btn-light" onclick="shareMedia('${videoUrl}', 'Video')">Compartir</button>
                <button type="button" class="btn btn-sm btn-light" onclick="copyMediaLink('${videoUrl}')">Copiar enlace</button>
            </div>

            <div class="message-time mt-1">
                <span>${time}</span>
                ${statusIcon}
            </div>
        </div>
    `;
            }

            return `
>>>>>>> main
            <div class="chat-text">Mensaje no soportado</div>
            <div class="message-time">
                <span>${time}</span>
                ${statusIcon}
            </div>
        `;
<<<<<<< HEAD
    }

    function showAiTyping() {
        if (!isAiChat) return;
        $('#aiTypingIndicator').show();
        scrollToBottom();
    }

    function hideAiTyping() {
        $('#aiTypingIndicator').hide();
    }

    function scrollToBottom() {
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    function formatTime(timestamp) {
        if (!timestamp) return '';

        const date = new Date(timestamp);
        if (isNaN(date.getTime())) return '';

        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';

        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;

        return hours + ':' + minutes + ' ' + ampm;
    }

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function nl2br(text) {
        return String(text).replace(/\n/g, '<br>');
    }

    function showToast(message, type = 'info') {
        if (typeof window.showToast === 'function') {
            window.showToast(message, type);
            return;
        }

        alert(message);
    }
});

function openImageModal(url) {
    document.getElementById('modalImage').src = url;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
=======
        }

        function shareMedia(url, title = 'Archivo') {
            if (navigator.share) {
                navigator.share({
                    title: title,
                    text: 'Mira este archivo',
                    url: url
                }).catch(err => {
                    console.error('Error compartiendo:', err);
                });
            } else {
                copyMediaLink(url);
                alert('Tu navegador no soporta compartir directamente. Se copió el enlace.');
            }
        }

        function copyMediaLink(url) {
            navigator.clipboard.writeText(url)
                .then(() => {
                    if (typeof window.showToast === 'function') {
                        window.showToast('Enlace copiado', 'success');
                    } else {
                        alert('Enlace copiado');
                    }
                })
                .catch(err => {
                    console.error('Error copiando enlace:', err);
                    alert('No se pudo copiar el enlace');
                });
        }

        function showAiTyping() {
            if (!isAiChat) return;
            $('#aiTypingIndicator').show();
            scrollToBottom();
        }

        function hideAiTyping() {
            $('#aiTypingIndicator').hide();
        }

        function scrollToBottom() {
            if (messagesContainer.length) {
                messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
            }
        }

        function formatTime(timestamp) {
            if (!timestamp) return '';

            const date = new Date(timestamp);
            if (isNaN(date.getTime())) return '';

            let hours = date.getHours();
            let minutes = date.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';

            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;

            return hours + ':' + minutes + ' ' + ampm;
        }

        function escapeHtml(text) {
            return String(text || '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function nl2br(text) {
            return String(text || '').replace(/\n/g, '<br>');
        }

        function showToast(message, type = 'info') {
            if (typeof window.showToast === 'function') {
                window.showToast(message, type);
                return;
            }

            alert(message);
        }
    });

    function openImageModal(url) {
        document.getElementById('modalImage').src = url;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
>>>>>>> main
</script>
@endpush