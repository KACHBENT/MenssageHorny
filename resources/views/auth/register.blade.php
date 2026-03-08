@extends('layouts.app')

@section('title', 'Registro')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/register.styles.css') }}">

    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="card shadow" style="max-width: 650px; border-radius: 20px;">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <img src="{{ asset('image/logo.png') }}" alt="WhatsApp-Sistemas" width="64">
                    <h3 class="mt-3 fw-bold" style="color: var(--whatsapp-teal);">Crear Cuenta</h3>
                    <p class="text-muted mb-0">Regístrate para comenzar a chatear</p>
                     @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success">
                        {{ session('success') }}
                    </div>
                @endif
                </div>
                <form method="POST" action="{{ route('register') }}" id="registerForm" novalidate>
                    @csrf

                    {{-- Nombre --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}" placeholder="Ingresa tu nombre" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Correo --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Correo</label>
                        <input type="email" name="email" id="email"
                            class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                            placeholder="Ingresa tu correo" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Términos --}}
                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror" id="terms"
                            name="terms" value="1" {{ old('terms') ? 'checked' : '' }} required>
                        <label class="form-check-label" for="terms">
                            Acepto los
                            <a href="#" class="text-decoration-none">términos y condiciones</a>
                        </label>
                        @error('terms')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Botón --}}
                    <button type="submit" class="btn btn-chat w-100">
                        <i class="fas fa-user-plus me-2"></i>Registrar usuario
                    </button>
                </form>

                {{-- Login --}}
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