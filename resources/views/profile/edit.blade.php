@extends('layouts.app')

@section('title', 'Mi Perfil')

@section('content')
<div class="w-100 h-100 overflow-auto p-4">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-semibold">
                    <i class="fas fa-user-circle me-2" style="color: var(--whatsapp-green);"></i>
                    Mi Perfil
                </h5>
            </div>
            <div class="card-body p-4">
                <!-- Avatar -->
                <div class="text-center mb-4">
                    <div class="position-relative d-inline-block">
                        <img src="{{ Auth::user()->avatar ? asset('storage/'.Auth::user()->avatar) : 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&size=120&background=25D366&color=fff' }}" 
                             class="rounded-circle border" 
                             alt="Avatar"
                             id="avatarPreview"
                             style="width: 120px; height: 120px; object-fit: cover;">
                        <button class="btn btn-success btn-sm rounded-circle position-absolute bottom-0 end-0" 
                                id="changeAvatarBtn"
                                title="Cambiar foto">
                            <i class="fas fa-camera"></i>
                        </button>
                        <input type="file" id="avatarInput" style="display: none;" accept="image/*">
                    </div>
                </div>

                <!-- Info Form -->
                <form id="profileForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium">Nombre</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ Auth::user()->name }}">
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="{{ Auth::user()->email }}">
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2" id="updateInfoBtn">
                        <i class="fas fa-save me-2"></i>
                        <span>Guardar Cambios</span>
                    </button>
                </form>

                <hr class="my-4">

                <!-- Change Password -->
                <h6 class="fw-semibold mb-3">Cambiar Contraseña</h6>
                <form id="passwordForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Contraseña Actual</label>
                        <input type="password" class="form-control" id="current_password" name="current_password">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Nueva Contraseña</label>
                        <input type="password" class="form-control" id="new_password" name="new_password">
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Confirmar Contraseña</label>
                        <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation">
                    </div>

                    <button type="submit" class="btn btn-outline-success w-100 py-2" id="updatePasswordBtn">
                        <i class="fas fa-key me-2"></i>
                        <span>Actualizar Contraseña</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Change avatar
    $('#changeAvatarBtn').click(function() {
        $('#avatarInput').click();
    });

    $('#avatarInput').change(function(e) {
        const file = e.target.files[0];
        if(!file) return;

        const formData = new FormData();
        formData.append('avatar', file);

        const btn = $(this);
        const originalText = $('#changeAvatarBtn').html();
        $('#changeAvatarBtn').html('<i class="fas fa-spinner fa-spin"></i>').prop('disabled', true);

        $.ajax({
            url: '{{ route("profile.update-avatar") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if(response.success) {
                    $('#avatarPreview').attr('src', response.avatar_url + '?t=' + new Date().getTime());
                    showToast('Foto actualizada correctamente', 'success');
                }
                $('#changeAvatarBtn').html(originalText).prop('disabled', false);
            },
            error: function(error) {
                handleError(error);
                $('#changeAvatarBtn').html(originalText).prop('disabled', false);
            }
        });
    });

    // Update profile info
    $('#profileForm').submit(function(e) {
        e.preventDefault();
        
        const btn = $('#updateInfoBtn');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Guardando...').prop('disabled', true);

        $.ajax({
            url: '{{ route("profile.update-info") }}',
            method: 'POST',
            data: {
                name: $('#name').val(),
                email: $('#email').val()
            },
            success: function(response) {
                if(response.success) {
                    showToast('Información actualizada', 'success');
                }
                btn.html(originalText).prop('disabled', false);
            },
            error: function(error) {
                handleError(error);
                btn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Update password
    $('#passwordForm').submit(function(e) {
        e.preventDefault();
        
        const btn = $('#updatePasswordBtn');
        const originalText = btn.html();
        btn.html('<i class="fas fa-spinner fa-spin me-2"></i>Actualizando...').prop('disabled', true);

        $.ajax({
            url: '{{ route("profile.update-password") }}',
            method: 'POST',
            data: {
                current_password: $('#current_password').val(),
                new_password: $('#new_password').val(),
                new_password_confirmation: $('#new_password_confirmation').val()
            },
            success: function(response) {
                if(response.success) {
                    showToast('Contraseña actualizada', 'success');
                    $('#passwordForm')[0].reset();
                }
                btn.html(originalText).prop('disabled', false);
            },
            error: function(error) {
                handleError(error);
                btn.html(originalText).prop('disabled', false);
            }
        });
    });
});
</script>
@endpush