@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.styles.css') }}">

<div class="w-100 h-100 d-flex align-items-center justify-content-center main">
    <div class="card shadow">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('image/logo.png') }}" alt="WhatsApp" width="64">
                <h3 class="mt-3 fw-bold" style="color: var(--whatsapp-teal);">Recuperar contraseña</h3>
                <p class="text-muted">Te enviaremos un enlace a tu correo.</p>

                @if (session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4">
                    <label for="email" class="form-label fw-medium">
                        <i class="fas fa-envelope me-2 text-muted"></i>Correo
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

                <button type="submit" class="btn btn-chat w-100 py-3 fw-bold">
                    <i class="fas fa-paper-plane me-2"></i>
                    Enviar enlace
                </button>
            </form>

            <div class="text-center mt-4">
                <a href="{{ route('login') }}" class="text-decoration-none" style="color: var(--whatsapp-green);">
                    Volver al login
                </a>
            </div>
        </div>
    </div>
</div>
@endsection