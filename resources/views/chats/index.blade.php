@extends('layouts.app')

@section('title', 'Chats')

@section('content')
    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&size=40&background=25D366&color=fff' }}"
                class="avatar me-3" alt="Avatar">
            <div class="flex-grow-1">
                <h6 class="mb-0 fw-semibold">{{ Auth::user()->name }}</h6>
                <small class="text-muted">
                    @if(Auth::user()->is_online)
                        <span class="text-success"><i class="fas fa-circle me-1" style="font-size: 8px;"></i>En línea</span>
                    @else
                        <span>Últ. vez
                            {{ Auth::user()->last_seen ? \Carbon\Carbon::parse(Auth::user()->last_seen)->diffForHumans() : 'desconocido' }}</span>
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
                    <li>
                        <hr class="dropdown-divider">
                    </li>
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

        <!-- Search Box -->
        <div class="search-box">
            <div class="position-relative">
                <i class="fas fa-search position-absolute text-muted" style="left: 15px; top: 12px;"></i>
                <input type="text" class="search-input ps-5" id="searchUsers" placeholder="Buscar usuario...">
            </div>

            <!-- NUEVO BOTON CREAR GRUPO -->
            <div class="mt-2">
                <button class="btn btn-success w-100 rounded-pill" data-bs-toggle="modal" data-bs-target="#createGroupModal">
                    <i class="fas fa-users me-2"></i>Crear Grupo
                </button>
            </div>

            <div id="searchResults" class="list-group mt-2" style="display: none;"></div>
        </div>

        <!-- Chats List -->
        <div class="flex-grow-1 overflow-auto" id="chatsList">
            @forelse($conversations as $conv)
                @php
                    $otherUser = $conv->user_one == Auth::id() ? $conv->userTwo : $conv->userOne;
                    $lastMsg = $conv->lastMessage;
                    $unreadCount = $conv->messages()
                        ->where('user_id', $otherUser->id)
                        ->where('is_read', false)
                        ->count();
                @endphp
                <a href="{{ route('chat.show', $otherUser->id) }}"
                    class="text-decoration-none text-dark contact-item d-flex align-items-center">
                    <div class="position-relative me-3">
                        <img src="{{ $otherUser->avatar ? asset('storage/' . $otherUser->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($otherUser->name) . '&size=50&background=128C7E&color=fff' }}"
                            class="avatar" alt="Avatar" style="width: 50px; height: 50px;">
                        @if($otherUser->is_online)
                            <span class="online-indicator"></span>
                        @endif
                    </div>
                    <div class="flex-grow-1 min-width-0">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-semibold text-truncate">{{ $otherUser->name }}</h6>
                            @if($lastMsg)
                                <small class="text-muted ms-2">{{ $lastMsg->created_at->format('H:i') }}</small>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted text-truncate" style="max-width: 180px;">
                                @if($lastMsg)
                                    @if($lastMsg->user_id == Auth::id())
                                        <span class="text-muted">Tú: </span>
                                    @endif
                                    @if($lastMsg->type == 'text')
                                        {{ Str::limit($lastMsg->message, 30) }}
                                    @elseif($lastMsg->type == 'image')
                                        <i class="fas fa-image me-1"></i>Imagen
                                    @elseif($lastMsg->type == 'video')
                                        <i class="fas fa-video me-1"></i>Video
                                    @endif
                                @else
                                    <span class="text-muted">Sin mensajes aún</span>
                                @endif
                            </small>
                            @if($unreadCount > 0)
                                <span class="badge rounded-pill"
                                    style="background-color: var(--whatsapp-green);">{{ $unreadCount }}</span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <h5>No tienes conversaciones</h5>
                    <p class="text-muted">Busca usuarios para comenzar a chatear</p>
                </div>
            @endforelse

            <div class="p-3 border-bottom">
                <a href="{{ route('chat.show', config('services.gemini.bot_user_id')) }}"
                    class="btn btn-chat w-100 rounded-pill">
                    <img src="{{ asset('image/robot.png') }}" alt="WhatsApp" width="32">
                    Habla con una asistente
                </a>
            </div>
        </div>
    </div>

    <!-- Chat Area -->
    <div class="chat-area d-flex align-items-center justify-content-center">
        <div class="empty-state">
            <i class="fas fa-comment-dots"></i>
            <h4>WhatsApp-Sistemas</h4>
            <p class="text-muted">Selecciona un chat para comenzar a conversar</p>
        </div>
    </div>

    <!-- MODAL CREAR GRUPO -->
    <div class="modal fade" id="createGroupModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Crear Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="text" id="groupName" class="form-control" placeholder="Nombre del grupo">
                </div>

                <div class="modal-footer">
                    <button class="btn btn-success" onclick="createGroup()">Crear</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
function createGroup(){

let name = document.getElementById("groupName").value;

if(!name){
alert("Debes escribir un nombre");
return;
}

fetch('/groups/create',{
method:'POST',
headers:{
'Content-Type':'application/json',
'X-CSRF-TOKEN':'{{ csrf_token() }}'
},
body:JSON.stringify({name:name})
})
.then(res=>res.json())
.then(data=>{
console.log(data);

if(data.group){
alert("Grupo creado correctamente");
location.reload();
}else{
alert("Error al crear grupo");
}

})
.catch(err=>{
console.error(err);
alert("Error del servidor");
});

}
</script>

<script>
$(document).ready(function () {
let searchTimeout;

$('#searchUsers').on('keyup', function () {

clearTimeout(searchTimeout);
const query = $(this).val().trim();

if (query.length < 2) {
$('#searchResults').hide().empty();
return;
}

$('#searchResults').html('<div class="text-center p-3"><div class="spinner-border spinner-border-sm text-success"></div> Buscando...</div>').show();

searchTimeout = setTimeout(() => {

$.ajax({
url: '{{ route("chat.search") }}',
method: 'GET',
data: { q: query },

success: function (users) {

if (users && users.length > 0) {

let html = '<div class="list-group mt-2" style="max-height:300px;overflow-y:auto;">';

users.forEach(user => {

html += `
<a href="/chat/${user.id}" class="list-group-item list-group-item-action d-flex align-items-center border-0 border-bottom">
<img src="${user.avatar ? '/storage/' + user.avatar : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(user.name) + '&size=40&background=25D366&color=fff'}"
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

}else{

$('#searchResults').html('<div class="alert alert-info mt-2">No se encontraron usuarios con "'+query+'"</div>').show();

}

},

error:function(){
$('#searchResults').html('<div class="alert alert-danger mt-2">Error al buscar usuarios</div>').show();
}

});

},500);

});

$(document).click(function (e) {

if (!$(e.target).closest('#searchUsers, #searchResults').length) {
$('#searchResults').hide();
}

});

});
</script>
@endpush