@extends('layouts.app')

@section('title', $otherUser->name)

@php
    $isAiChat = (int) $otherUser->id === (int) config('services.gemini.bot_user_id');
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
    </div>
</div>

<input type="hidden" id="conversationId" value="{{ $conversation->id }}">
<input type="hidden" id="otherUserId" value="{{ $otherUser->id }}">
<input type="hidden" id="isAiChat" value="{{ $isAiChat ? 1 : 0 }}">

<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0 position-relative">
                <button type="button"
                        class="btn-close btn-close-white position-absolute top-0 end-0 m-3"
                        data-bs-dismiss="modal"></button>
                <img src="" id="modalImage" class="img-fluid w-100" alt="Imagen">
            </div>
        </div>
    </div>
</div>
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
            <div class="message ${type}" data-message-id="${message.id}">
                <div class="message-content">
                    ${renderMessageContent(message, type)}
                </div>
            </div>
        `;

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
                <div class="chat-text">${nl2br(escapeHtml(message.message || ''))}</div>
                <div class="message-time">
                    <span>${time}</span>
                    ${statusIcon}
                </div>
            `;
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
            <div class="chat-text">Mensaje no soportado</div>
            <div class="message-time">
                <span>${time}</span>
                ${statusIcon}
            </div>
        `;
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
</script>
@endpush