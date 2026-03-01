@extends('layouts.app')

@section('title', $otherUser->name)

@section('content')
<!-- Sidebar -->
<div class="sidebar">
    <!-- Header -->
    <div class="sidebar-header">
        <img src="{{ Auth::user()->avatar ? asset('storage/'.Auth::user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&size=40&background=25D366&color=fff' }}" 
             class="avatar me-3" alt="Avatar">
        <div class="flex-grow-1">
            <h6 class="mb-0 fw-semibold">{{ Auth::user()->name }}</h6>
            <small class="text-muted">
                @if(Auth::user()->is_online)
                    <span class="text-success"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>En línea</span>
                @else
                    <span>Últ. vez {{ Auth::user()->last_seen ? \Carbon\Carbon::parse(Auth::user()->last_seen)->diffForHumans() : 'desconocido' }}</span>
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
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="fas fa-user me-2"></i>Perfil</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt me-2"></i>Salir</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>

    <!-- Search Box -->
    <div class="search-box">
        <div class="position-relative">
            <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
            <input type="text" class="search-input ps-5" id="searchUsers" placeholder="Buscar usuario...">
        </div>
        <div id="searchResults" class="mt-2" style="display: none;"></div>
    </div>

    <!-- Chats List -->
    <div class="flex-grow-1 overflow-auto" id="chatsList">
        @foreach($conversations as $conv)
            @php
                $contact = $conv->user_one == Auth::id() ? $conv->userTwo : $conv->userOne;
                $lastMsg = $conv->lastMessage;
                $active = $contact->id == $otherUser->id ? 'active' : '';
                $unreadCount = $conv->messages()
                    ->where('user_id', $contact->id)
                    ->where('is_read', false)
                    ->count();
            @endphp
            <a href="{{ route('chat.show', $contact->id) }}" 
               class="text-decoration-none text-dark contact-item d-flex align-items-center {{ $active }}">
                <div class="position-relative me-3">
                    <img src="{{ $contact->avatar ? asset('storage/'.$contact->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($contact->name).'&size=50&background=128C7E&color=fff' }}" 
                         class="avatar" alt="Avatar" style="width: 50px; height: 50px;">
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
                                @if($lastMsg->type == 'text')
                                    {{ Str::limit($lastMsg->message, 20) }}
                                @elseif($lastMsg->type == 'image')
                                    <i class="fas fa-image me-1"></i>Imagen
                                @elseif($lastMsg->type == 'video')
                                    <i class="fas fa-video me-1"></i>Video
                                @endif
                            @endif
                        </small>
                        @if($unreadCount > 0)
                            <span class="badge rounded-pill" style="background-color: var(--whatsapp-green);">{{ $unreadCount }}</span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>
</div>

<!-- Chat Area -->
<div class="chat-area">
    <!-- Chat Header -->
    <div class="chat-header">
        <div class="d-flex align-items-center">
            <div class="position-relative me-3">
                <img src="{{ $otherUser->avatar ? asset('storage/'.$otherUser->avatar) : 'https://ui-avatars.com/api/?name='.urlencode($otherUser->name).'&size=40&background=128C7E&color=fff' }}" 
                     class="avatar" alt="Avatar">
                @if($otherUser->is_online)
                    <span class="online-indicator"></span>
                @endif
            </div>
            <div>
                <h6 class="mb-0 fw-semibold">{{ $otherUser->name }}</h6>
                <small class="text-muted" id="userStatus">
                    @if($otherUser->is_online)
                        <span class="text-success">En línea</span>
                    @else
                        Últ. vez {{ $otherUser->last_seen ? \Carbon\Carbon::parse($otherUser->last_seen)->diffForHumans() : 'desconocido' }}
                    @endif
                </small>
            </div>
        </div>
    </div>

    <!-- Messages Container -->
    <div class="messages-container" id="messagesContainer">
        @foreach($messages as $msg)
            <div class="message {{ $msg->user_id == Auth::id() ? 'sent' : 'received' }}" data-message-id="{{ $msg->id }}">
                <div class="message-content">
                    @if($msg->type == 'text')
                        <p class="mb-1">{{ $msg->message }}</p>
                        <div class="message-time">
                            <span>{{ $msg->created_at->format('H:i') }}</span>
                            @if($msg->user_id == Auth::id())
                                <i class="fas fa-check{{ $msg->is_read ? '-double' : '' }} message-status"></i>
                            @endif
                        </div>
                    @elseif($msg->type == 'image')
                        <div class="image-preview-container">
                            <img src="{{ $msg->file_url }}" 
                                 class="img-fluid rounded" 
                                 style="max-width: 250px; max-height: 250px; cursor: pointer;" 
                                 onclick="openImageModal('{{ $msg->file_url }}')"
                                 alt="Imagen"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/250?text=Error+al+cargar+imagen';">
                            <div class="message-time mt-1">
                                <span>{{ $msg->created_at->format('H:i') }}</span>
                                @if($msg->user_id == Auth::id())
                                    <i class="fas fa-check{{ $msg->is_read ? '-double' : '' }} message-status"></i>
                                @endif
                            </div>
                        </div>
                    @elseif($msg->type == 'video')
                        <div class="video-preview-container">
                            <video controls class="rounded" style="max-width: 300px; max-height: 300px;">
                                <source src="{{ $msg->file_url }}" type="video/mp4">
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

    <!-- Message Input -->
    <div class="message-input-area">
        <button type="button" class="btn-icon" id="attachFileBtn" title="Adjuntar archivo">
            <i class="fas fa-plus-circle"></i>
        </button>
        
        <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*">
        
        <input type="text" 
               class="message-input" 
               id="messageInput" 
               placeholder="Escribe un mensaje..."
               autocomplete="off">
        
        <button type="button" class="btn-icon btn-send" id="sendMessageBtn" disabled>
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>
</div>

<!-- Hidden data -->
<input type="hidden" id="conversationId" value="{{ $conversation->id }}">
<input type="hidden" id="otherUserId" value="{{ $otherUser->id }}">

<!-- Modal para ver imágenes -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark">
            <div class="modal-body p-0 position-relative">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Close"></button>
                <img src="" id="modalImage" class="img-fluid w-100" alt="Imagen">
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const conversationId = $('#conversationId').val();
    const messagesContainer = $('#messagesContainer');
    const messageInput = $('#messageInput');
    const sendBtn = $('#sendMessageBtn');
    let lastMessageId = {{ $messages->last()->id ?? 0 }};

    // Scroll to bottom
    messagesContainer.scrollTop(messagesContainer[0].scrollHeight);

    // Enable/disable send button
    messageInput.on('input', function() {
        sendBtn.prop('disabled', $(this).val().trim() === '');
    });

    // Send message on Enter
    messageInput.on('keypress', function(e) {
        if(e.which === 13 && !e.shiftKey) {
            e.preventDefault();
            if(!sendBtn.prop('disabled')) {
                sendMessage();
            }
        }
    });

    // Send message on button click
    sendBtn.on('click', sendMessage);

    // Send message function
    function sendMessage() {
        const message = messageInput.val().trim();
        if(!message) return;

        const originalText = sendBtn.html();
        sendBtn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: '{{ route("messages.send") }}',
            method: 'POST',
            data: {
                conversation_id: conversationId,
                message: message
            },
            success: function(response) {
                if(response.success) {
                    messageInput.val('');
                    sendBtn.html('<i class="fas fa-paper-plane"></i>').prop('disabled', true);
                    appendMessage(response.data, 'sent');
                    lastMessageId = response.data.id;
                }
            },
            error: function(error) {
                console.error('Error:', error);
                showToast('Error al enviar mensaje', 'error');
                sendBtn.html(originalText).prop('disabled', false);
            }
        });
    }

    // File attachment
    $('#attachFileBtn').click(function() {
        $('#fileInput').click();
    });

    $('#fileInput').change(function(e) {
        const file = e.target.files[0];
        if(!file) return;

        const formData = new FormData();
        formData.append('file', file);
        formData.append('conversation_id', conversationId);

        const btn = $('#attachFileBtn');
        const originalHtml = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        // Mostrar preview temporal
        showToast('Subiendo archivo...', 'info');

        $.ajax({
            url: '{{ route("messages.send-file") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    appendMessage(response.data, 'sent');
                    lastMessageId = response.data.id;
                    $('#fileInput').val('');
                    showToast('Archivo enviado', 'success');
                }
                btn.html(originalHtml).prop('disabled', false);
            },
            error: function(error) {
                console.error('Error:', error);
                showToast('Error al enviar archivo', 'error');
                btn.html(originalHtml).prop('disabled', false);
                $('#fileInput').val('');
            }
        });
    });

    // Append message function
    function appendMessage(message, type) {
        let messageHtml = '';
        
        if(message.type == 'text') {
            messageHtml = `
                <p class="mb-1">${message.message}</p>
                <div class="message-time">
                    <span>${formatTime(message.created_at)}</span>
                    <i class="fas fa-check message-status"></i>
                </div>
            `;
        } else if(message.type == 'image') {
            messageHtml = `
                <div class="image-preview-container">
                    <img src="${message.file_url}" 
                         class="img-fluid rounded" 
                         style="max-width: 250px; max-height: 250px; cursor: pointer;" 
                         onclick="openImageModal('${message.file_url}')"
                         alt="Imagen"
                         onerror="this.onerror=null; this.src='https://via.placeholder.com/250?text=Error+al+cargar+imagen';">
                    <div class="message-time mt-1">
                        <span>${formatTime(message.created_at)}</span>
                        <i class="fas fa-check message-status"></i>
                    </div>
                </div>
            `;
        } else if(message.type == 'video') {
            messageHtml = `
                <div class="video-preview-container">
                    <video controls class="rounded" style="max-width: 300px; max-height: 300px;">
                        <source src="${message.file_url}" type="video/mp4">
                        Tu navegador no soporta el elemento de video.
                    </video>
                    <div class="message-time mt-1">
                        <span>${formatTime(message.created_at)}</span>
                        <i class="fas fa-check message-status"></i>
                    </div>
                </div>
            `;
        }
        
        const fullHtml = `
            <div class="message ${type}" data-message-id="${message.id}">
                <div class="message-content">
                    ${messageHtml}
                </div>
            </div>
        `;
        
        messagesContainer.append(fullHtml);
        messagesContainer.scrollTop(messagesContainer[0].scrollHeight);
    }

    // Format time function
    function formatTime(timestamp) {
        const date = new Date(timestamp);
        let hours = date.getHours();
        let minutes = date.getMinutes();
        const ampm = hours >= 12 ? 'PM' : 'AM';
        hours = hours % 12;
        hours = hours ? hours : 12;
        minutes = minutes < 10 ? '0' + minutes : minutes;
        return hours + ':' + minutes + ' ' + ampm;
    }

    // Check for new messages every 2 seconds
    function checkNewMessages() {
        $.ajax({
            url: `/chat/messages/${conversationId}/since/${lastMessageId}`,
            method: 'GET',
            success: function(messages) {
                if(messages.length > 0) {
                    messages.forEach(msg => {
                        const type = msg.user_id == {{ Auth::id() }} ? 'sent' : 'received';
                        appendMessage(msg, type);
                        lastMessageId = msg.id;
                    });
                    
                    // Mark as read
                    $.post('{{ route("messages.mark-read") }}', {
                        conversation_id: conversationId
                    });
                }
            },
            error: function(error) {
                console.error('Error checking messages:', error);
            }
        });
    }

    setInterval(checkNewMessages, 2000);

    // Search users
    let searchTimeout;
    $('#searchUsers').on('keyup', function() {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        if(query.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }
        
        $('#searchResults').html('<div class="text-center p-2"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>').show();
        
        searchTimeout = setTimeout(() => {
            $.ajax({
                url: '{{ route("chat.search") }}',
                method: 'GET',
                data: { q: query },
                success: function(users) {
                    if(users && users.length > 0) {
                        let html = '<div class="list-group mt-2" style="max-height: 300px; overflow-y: auto;">';
                        users.forEach(user => {
                            html += `
                                <a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
                                    <img src="${user.avatar ? '/storage/'+user.avatar : 'https://ui-avatars.com/api/?name='+encodeURIComponent(user.name)+'&size=40&background=25D366&color=fff'}" 
                                         class="rounded-circle me-3" width="40" height="40">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold">${user.name}</h6>
                                            ${user.is_online ? '<span class="badge bg-success">En línea</span>' : ''}
                                        </div>
                                        <small class="text-muted">${user.email}</small>
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
                error: function() {
                    $('#searchResults').html('<div class="alert alert-danger mt-2">Error en la búsqueda</div>').show();
                }
            });
        }, 500);
    });

    // Close search results when clicking outside
    $(document).click(function(e) {
        if (!$(e.target).closest('#searchUsers, #searchResults').length) {
            $('#searchResults').hide();
        }
    });
});

// Global function for image modal
function openImageModal(url) {
    document.getElementById('modalImage').src = url;
    new bootstrap.Modal(document.getElementById('imageModal')).show();
}
</script>
@endpush