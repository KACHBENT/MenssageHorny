@extends('layouts.app')

@section('title', 'Chats')

@section('content')
<div class="container-fluid px-0">
    <div class="chat-layout">
        <!-- Sidebar -->
        <aside class="sidebar chat-sidebar">
            <div class="sidebar-header">
                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=40&background=25D366&color=fff' }}"
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
                            <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                <i class="fas fa-user me-2"></i>Mi Perfil
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item align-items-center" href="{{ route('settingfaces.settingfaceprofile') }}" style="display:flex; gap: 10px;">
                                <img src="{{ asset('image/icons/ar_on_you.svg') }}" alt="settingFaces" width="20" class="black-filter">Configuración faciales
                            </a>
                        </li>
                         <li><hr class="dropdown-divider"></li>
                         <li class="settingface">
                            <a class="dropdown-item align-items-center" href="{{ route('biometric.settings') }}" style="display:flex; gap: 10px;">
                                <img src="{{ asset('image/icons/fingerprint.svg') }}" alt="settingfingerprints" width="20" class="black-filter">Configuración huella
                            </a>
                        </li>
                         <li class="settingface"><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="fas fa-sign-out-alt me-2"></i>Cerrar sesión
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
                    @endphp

                    <a href="{{ route('groups.show', $groupItem->id) }}"
                       class="text-decoration-none text-dark contact-item d-flex align-items-center">
                        <div class="position-relative me-3 flex-shrink-0">
                            <img src="{{ $groupItem->image ? asset('storage/' . $groupItem->image) : 'https://ui-avatars.com/api/?name=' . urlencode($groupItem->name) . '&size=50&background=6c757d&color=fff' }}"
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

                @forelse($conversations as $conv)
                    @php
                        $otherUser = $conv->user_one == Auth::id() ? $conv->userTwo : $conv->userOne;
                        $lastMsg = $conv->lastMessage;
                        $unreadCount = 0;

                        if ($otherUser) {
                            $unreadCount = $conv->messages()
                                ->where('user_id', $otherUser->id)
                                ->where('is_read', false)
                                ->count();
                        }
                    @endphp

                    @if($otherUser)
                        <a href="{{ route('chat.show', $otherUser->id) }}"
                           class="text-decoration-none text-dark contact-item d-flex align-items-center">
                            <div class="position-relative me-3 flex-shrink-0">
                                <img src="{{ $otherUser->avatar ? asset('storage/' . $otherUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) . '&size=50&background=128C7E&color=fff' }}"
                                     class="avatar"
                                     alt="Avatar"
                                     style="width: 50px; height: 50px;">
                                @if($otherUser->is_online)
                                    <span class="online-indicator"></span>
                                @endif
                            </div>

                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-center gap-2">
                                    <h6 class="mb-0 fw-semibold text-truncate">{{ $otherUser->name }}</h6>
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
                                                {{ \Illuminate\Support\Str::limit($lastMsg->message, 30) }}
                                            @elseif($lastMsg->type === 'image')
                                                <i class="fas fa-image me-1"></i>Imagen
                                            @elseif($lastMsg->type === 'video')
                                                <i class="fas fa-video me-1"></i>Video
                                            @endif
                                        @else
                                            <span class="text-muted">Sin mensajes aún</span>
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
                @empty
                    <div class="empty-state">
                        <i class="fas fa-comments"></i>
                        <h5>No tienes conversaciones</h5>
                        <p class="text-muted">Busca usuarios para comenzar a chatear</p>
                    </div>
                @endforelse
            </div>
        </aside>

        <!-- Placeholder -->
        <section class="chat-area chat-area-placeholder d-none d-md-flex align-items-center justify-content-center">
            <div class="empty-state">
                <i class="fas fa-comment-dots"></i>
                <h4>WhatsApp-Sistemas</h4>
                <p class="text-muted">Selecciona un chat o grupo para comenzar a conversar</p>
            </div>
        </section>
    </div>
</div>

<input type="hidden" id="authUserId" value="{{ Auth::id() }}">
<input type="hidden" id="csrfToken" value="{{ csrf_token() }}">
<input type="hidden" id="groupsStoreUrl" value="{{ route('groups.store') }}">
<input type="hidden" id="searchUsersUrl" value="{{ route('chat.search') }}">

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
$(document).ready(function () {
    let searchTimeout;
    let groupSearchTimeout;

    const authUserId = Number($('#authUserId').val() || 0);
    const csrfToken = $('#csrfToken').val();
    const groupsStoreUrl = $('#groupsStoreUrl').val();
    const searchUsersUrl = $('#searchUsersUrl').val();
    const selectedUsers = new Map();

    renderSelectedGroupUsers();

    $('#searchUsers').on('keyup', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#searchResults').hide().empty();
            return;
        }

        $('#searchResults').html(
            '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>'
        ).show();

        searchTimeout = setTimeout(() => {
            $.ajax({
                url: searchUsersUrl,
                method: 'GET',
                data: { q: query },
                success: function (users) {
                    if (users && users.length > 0) {
                        let html = '<div class="list-group mt-2" style="max-height:300px;overflow-y:auto;">';

                        users.forEach(user => {
                            const avatar = user.avatar
                                ? '/storage/' + user.avatar
                                : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                            html += `
                                <a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
                                    <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" alt="Avatar">
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0 fw-semibold text-truncate">${escapeHtml(user.name)}</h6>
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
                        $('#searchResults').html('<div class="alert alert-info mt-2 mb-0">No se encontraron usuarios</div>').show();
                    }
                },
                error: function () {
                    $('#searchResults').html('<div class="alert alert-danger mt-2 mb-0">Error al buscar usuarios</div>').show();
                }
            });
        }, 400);
    });

    $('#groupUserSearch').on('keyup', function () {
        clearTimeout(groupSearchTimeout);
        const query = $(this).val().trim();

        if (query.length < 2) {
            $('#groupUserResults').hide().empty();
            return;
        }

        $('#groupUserResults').html(
            '<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>'
        ).show();

        groupSearchTimeout = setTimeout(() => {
            $.ajax({
                url: searchUsersUrl,
                method: 'GET',
                data: { q: query },
                success: function (users) {
                    const filteredUsers = (users || []).filter(user =>
                        parseInt(user.id) !== authUserId && !selectedUsers.has(parseInt(user.id))
                    );

                    if (!filteredUsers.length) {
                        $('#groupUserResults').html('<div class="alert alert-info mb-0">No hay usuarios disponibles</div>').show();
                        return;
                    }

                    let html = '';

                    filteredUsers.forEach(user => {
                        const avatar = user.avatar
                            ? '/storage/' + user.avatar
                            : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff';

                        html += `
                            <button type="button"
                                    class="list-group-item list-group-item-action d-flex align-items-center add-group-user"
                                    data-user-id="${user.id}"
                                    data-user-name="${escapeHtml(user.name)}"
                                    data-user-email="${escapeHtml(user.email ?? '')}">
                                <img src="${avatar}" class="rounded-circle me-3" width="40" height="40" alt="Avatar">
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
                error: function () {
                    $('#groupUserResults').html('<div class="alert alert-danger mb-0">Error al buscar usuarios</div>').show();
                }
            });
        }, 350);
    });

    $(document).on('click', '.add-group-user', function () {
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

    $(document).on('click', '.remove-group-user', function () {
        const userId = parseInt($(this).data('user-id'));
        selectedUsers.delete(userId);
        renderSelectedGroupUsers();
        $('#groupUserSearch').trigger('keyup');
    });

    $('#createGroupBtn').on('click', function () {
        createGroup();
    });

    $('#createGroupModal').on('hidden.bs.modal', function () {
        resetGroupModal();
    });

    $(document).click(function (e) {
        if (!$(e.target).closest('#searchUsers, #searchResults').length) {
            $('#searchResults').hide();
        }
    });

    function createGroup() {
        const name = $('#groupName').val().trim();
        const description = $('#groupDescription').val().trim();
        const userIds = Array.from(selectedUsers.keys());

        if (!name) {
            alert('Debes escribir un nombre para el grupo');
            return;
        }

        if (userIds.length === 0) {
            alert('Debes seleccionar al menos un usuario');
            return;
        }

        if (!groupsStoreUrl) {
            alert('La ruta groups.store está vacía');
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

            alert(data.message || 'No se pudo crear el grupo');
            $('#createGroupBtn').prop('disabled', false).html('Crear');
        })
        .catch(error => {
            console.error(error);
            alert(error.message || 'Error al crear el grupo');
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

    function escapeHtml(text) {
        return String(text || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }
});
</script>
@endpush