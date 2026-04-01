@php
    $currentUserChatId = $currentUserChatId ?? null;
    $currentGroupId = $currentGroupId ?? null;
@endphp

<aside class="sidebar chat-sidebar">
    <div class="sidebar-header">
        <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=40&background=25D366&color=fff' }}"
            class="avatar me-3" alt="Avatar">

        <div class="flex-grow-1 min-w-0">
            <h6 class="mb-0 fw-semibold text-truncate">{{ Auth::user()->name }}</h6>
            <small class="text-muted">
                @if(Auth::user()->is_online)
                    <span class="text-success">
                        <i class="fas fa-circle me-1" style="font-size: 8px;"></i>En línea
                    </span>
                @else
                    <span>
                        Últ. vez
                        {{ Auth::user()->last_seen ? \Carbon\Carbon::parse(Auth::user()->last_seen)->diffForHumans() : 'desconocido' }}
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
                class="btn btn-chat w-100 rounded-pill d-flex align-items-center justify-content-center gap-2"
                data-bs-toggle="modal" data-bs-target="#createGroupModal">
                <i class="fas fa-users"></i>
                <span>Crear Grupo</span>
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
        <div class="px-3 pt-3 pb-2 small text-uppercase fw-bold text-muted">Grupos</div>

        @forelse($groups ?? [] as $groupItem)
            @php
                $lastGroupMsg = $groupItem->lastMessage;
                $activeGroup = (int) $currentGroupId === (int) $groupItem->id ? 'active' : '';
            @endphp

            <a href="{{ route('groups.show', $groupItem->id) }}"
                class="text-decoration-none text-dark contact-item d-flex align-items-center {{ $activeGroup }}">
                <div class="position-relative me-3 flex-shrink-0">
                    <img src="{{ $groupItem->image ? asset('storage/' . $groupItem->image) : 'https://ui-avatars.com/api/?name=' . urlencode($groupItem->name) . '&size=50&background=6c757d&color=fff' }}"
                        class="avatar" alt="Grupo" style="width: 50px; height: 50px;">
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
            <div class="px-3 pb-2 text-muted small">No perteneces a grupos todavía</div>
        @endforelse

        <div class="px-3 pt-3 pb-2 small text-uppercase fw-bold text-muted">Chats privados</div>

        @forelse($conversations ?? [] as $conv)
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

                $activeUser = $otherUser && (int) $currentUserChatId === (int) $otherUser->id ? 'active' : '';
            @endphp

            @if($otherUser)
                <a href="{{ route('chat.show', $otherUser->id) }}"
                    class="text-decoration-none text-dark contact-item d-flex align-items-center {{ $activeUser }}">
                    <div class="position-relative me-3 flex-shrink-0">
                        <img src="{{ $otherUser->avatar ? asset('storage/' . $otherUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) . '&size=50&background=128C7E&color=fff' }}"
                            class="avatar" alt="Avatar" style="width: 50px; height: 50px;">
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