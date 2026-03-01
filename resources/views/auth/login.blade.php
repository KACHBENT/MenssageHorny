@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<div class="w-100 h-100 d-flex align-items-center justify-content-center">
    <div class="card shadow" style="width: 100%; max-width: 400px;">
        <div class="card-body p-5">
            <!-- Logo -->
            <div class="text-center mb-4">
                <img src="https://img.icons8.com/color/96/whatsapp--v1.png" alt="WhatsApp" width="64">
                <h3 class="mt-3 fw-bold" style="color: var(--whatsapp-teal);">WhatsApp-Chris</h3>
                <p class="text-muted">Inicia sesión para continuar</p>
            </div>

            <!-- Formulario de Login -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf
                
                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">
                        <i class="fas fa-envelope me-2 text-muted"></i>Email
                    </label>
                    <input type="email" 
                           class="form-control form-control-lg @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           placeholder="tu@email.com"
                           required 
                           autofocus>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="form-label fw-medium">
                        <i class="fas fa-lock me-2 text-muted"></i>Contraseña
                    </label>
                    <div class="input-group">
                        <input type="password" 
                               class="form-control form-control-lg @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               placeholder="••••••••"
                               required>
                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Recordarme</label>
                </div>

                <!-- Botón Login -->
                <button type="submit" class="btn btn-success w-100 py-3 fw-bold" id="loginBtn">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    <span>Iniciar Sesión</span>
                </button>
            </form>

            <!-- Registro (opcional) -->
            <div class="text-center mt-4">
                <p class="text-muted mb-0">¿No tienes cuenta?</p>
                <a href="{{ route('register') }}" class="text-decoration-none" style="color: var(--whatsapp-green);">
                    Regístrate aquí
                </a>
            </div>

            <!-- Usuarios de prueba (solo para desarrollo) -->
            <div class="mt-4 pt-3 border-top">
                <small class="text-muted d-block mb-2">Usuarios de prueba:</small>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-sm btn-outline-success test-user" data-email="chris@whatsapp.com" data-pass="12345678">
                        Chris Admin
                    </button>
                    <button class="btn btn-sm btn-outline-success test-user" data-email="ana@example.com" data-pass="12345678">
                        Ana García
                    </button>
                    <button class="btn btn-sm btn-outline-success test-user" data-email="carlos@example.com" data-pass="12345678">
                        Carlos López
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle password visibility
    $('#togglePassword').click(function() {
        const password = $('#password');
        const icon = $(this).find('i');
        
        if (password.attr('type') === 'password') {
            password.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            password.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Test users autofill
    $('.test-user').click(function() {
        const email = $(this).data('email');
        const pass = $(this).data('pass');
        
        $('#email').val(email);
        $('#password').val(pass);
        
        // Opcional: auto-submit
        // $('#loginForm').submit();
    });

    // Loading state on submit
    $('#loginForm').submit(function() {
        const btn = $('#loginBtn');
        btn.prop('disabled', true);
        btn.find('span').text('Iniciando sesión...');
        btn.append('<span class="spinner-border spinner-border-sm ms-2"></span>');
    });
});
</script>
@endpush