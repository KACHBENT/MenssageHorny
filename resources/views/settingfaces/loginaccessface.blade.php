@extends('layouts.app')

@section('title', 'Iniciar sesión con rostro')

@section('content')
<link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/face-auth.styles.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<section class="container-main">
    <div class="face-shell">
        <div class="card face-card">
            <div class="card-header d-flex align-items-center gap-3">
                <img src="{{ asset('image/icons/ar_on_you.svg') }}" alt="loginFace" width="34" class="white">
                <div>
                    <h4 class="face-title">Acceso facial</h4>
                    <p class="face-subtitle">Inicia sesión con una validación rápida.</p>
                </div>
            </div>

            <div class="card-body">
                <div class="mb-3">
                    <label for="identity" class="face-form-label">Correo o usuario (opcional)</label>
                    <input
                        type="text"
                        id="identity"
                        class="form-control face-input"
                        placeholder="Ejemplo: usuario@correo.com o Kachbent">
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" id="remember">
                    <label class="form-check-label" for="remember">
                        Recordarme
                    </label>
                </div>

                <div class="container-capture-face">
                    <video id="loginFaceVideo" autoplay muted playsinline style="object-fit: cover;"></video>
                    <canvas id="loginFaceCanvas" class="position-absolute top-0 start-0"></canvas>
                </div>

                <div class="face-actions">
                    <button type="button" id="btnStartLoginCamera" class="btn btn-wa btn-wa-primary">
                        Activar cámara
                    </button>

                    <button type="button" id="btnVerifyFace" class="btn btn-wa btn-wa-secondary" disabled>
                        Iniciar sesión con rostro
                    </button>
                </div>

                <div id="loginFaceStatus" class="face-status">
                    Presiona <strong>Activar cámara</strong> para iniciar la validación.
                </div>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="face-link-back">
                        Volver al login tradicional
                    </a>
                </div>
            </div>

            <div class="face-footer">
                Asegúrate de tener buena iluminación y mirar de frente.
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script src="{{ asset('js/api-face.js') }}"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    FaceBio.initLogin({
        videoId: 'loginFaceVideo',
        canvasId: 'loginFaceCanvas',
        statusId: 'loginFaceStatus',
        startButtonId: 'btnStartLoginCamera',
        verifyButtonId: 'btnVerifyFace',
        identityId: 'identity',
        rememberId: 'remember',
        modelsUrl: @json('https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js/weights'),
        verifyUrl: @json(route('settingfaces.verifyfacelogin'))
    });
});
</script>
@endpush