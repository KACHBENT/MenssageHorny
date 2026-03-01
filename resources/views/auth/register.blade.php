@extends('layouts.app')

@section('title', 'Registro')

@section('content')
<div class="w-100 h-100 d-flex align-items-center justify-content-center">
    <div class="card shadow" style="width: 100%; max-width: 450px;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="https://img.icons8.com/color/96/whatsapp--v1.png" alt="WhatsApp" width="64">
                <h3 class="mt-3 fw-bold" style="color: var(--whatsapp-teal);">Crear Cuenta</h3>
                <p class="text-muted">Regístrate para comenzar a chatear</p>
            </div>

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <!-- Nombre -->
                <div class="mb-3">
                    <label for="name" class="form-label">Nombre completo</label>
                    <input type="text" 
                           class="form-control form-control-lg @error('name') is-invalid @enderror" 
                           id="name" 
                           name="name" 
                           value="{{ old('name') }}" 
                           required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" 
                           class="form-control form-control-lg @error('email') is-invalid @enderror" 
                           id="email" 
                           name="email" 
                           value="{{ old('email') }}" 
                           required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" 
                           class="form-control form-control-lg @error('password') is-invalid @enderror" 
                           id="password" 
                           name="password" 
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Confirmar Contraseña</label>
                    <input type="password" 
                           class="form-control form-control-lg" 
                           id="password_confirmation" 
                           name="password_confirmation" 
                           required>
                </div>

                <!-- Términos -->
                <div class="mb-4 form-check">
                    <input type="checkbox" class="form-check-input" id="terms" required>
                    <label class="form-check-label" for="terms">
                        Acepto los <a href="#" class="text-decoration-none">términos y condiciones</a>
                    </label>
                </div>

                <!-- Botón Registro -->
                <button type="submit" class="btn btn-success w-100 py-3 fw-bold" id="registerBtn">
                    <i class="fas fa-user-plus me-2"></i>
                    <span>Crear Cuenta</span>
                </button>
            </form>

            <!-- Login -->
            <div class="text-center mt-4">
                <p class="text-muted mb-0">¿Ya tienes cuenta?</p>
                <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--whatsapp-green);">
                    Inicia sesión aquí
                </a>
            </div>
        </div>
    </div>
</div>
@endsection