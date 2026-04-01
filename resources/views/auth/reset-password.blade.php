@extends('layouts.app')

@section('title', 'Restablecer contraseña')

@section('content')
<link rel="stylesheet" href="{{ asset('css/login.styles.css') }}">

<div class="w-100 h-100 d-flex align-items-center justify-content-center main">
    <div class="card shadow">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <img src="{{ asset('image/logo.png') }}" alt="WhatsApp" width="64">
                <h3 class="mt-3 fw-bold" style="color: var(--whatsapp-teal);">Nueva contraseña</h3>
                <p class="text-muted">Escribe tu nueva contraseña.</p>

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

            <form method="POST" action="{{ route('password.update') }}">
                @csrf

                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label fw-medium">
                        <i class="fas fa-envelope me-2 text-muted"></i>Correo
                    </label>
                    <input type="email"
                           class="form-control form-control-lg"
                           id="email"
                           name="email"
                           value="{{ old('email', $email) }}"
                           required
                           readonly>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label fw-medium">
                        <i class="fas fa-lock me-2 text-muted"></i>Nueva contraseña
                    </label>
                    <input type="password"
                           class="form-control form-control-lg"
                           id="password"
                           name="password"
                           placeholder="••••••••"
                           required>
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label fw-medium">
                        <i class="fas fa-lock me-2 text-muted"></i>Confirmar contraseña
                    </label>
                    <input type="password"
                           class="form-control form-control-lg"
                           id="password_confirmation"
                           name="password_confirmation"
                           placeholder="••••••••"
                           required>
                </div>

                <button type="submit" class="btn btn-chat w-100 py-3 fw-bold">
                    <i class="fas fa-key me-2"></i>
                    Guardar nueva contraseña
                </button>
            </form>
        </div>
    </div>
</div>
@endsection