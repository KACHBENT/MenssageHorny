@extends('layouts.app')

@section('title', 'Configuración facial')

@section('content')
<link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/face-auth.styles.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<section class="container-main">
    <div class="face-shell">
        <div class="card face-card">
            <div class="card-header d-flex align-items-center gap-3">
                <img src="{{ asset('image/icons/ar_on_you.svg') }}" alt="settingFaces" width="34" class="white">
                <div>
                    <h4 class="face-title">Configuración facial</h4>
                    <p class="face-subtitle">Protege tu acceso con un registro biométrico.</p>
                </div>
            </div>

            <div class="card-body">
                <div class="face-state text-center">
                    <p class="mb-2">
                        <strong>Estado:</strong>
                        @if($user->faceBiometric && $user->faceBiometric->is_enabled)
                            <span class="badge badge-wa-ok">Rostro configurado</span>
                        @else
                            <span class="badge badge-wa-off">Sin configurar</span>
                        @endif
                    </p>

                    @if($user->faceBiometric && $user->faceBiometric->registered_at)
                        <small class="text-muted">
                            Registrado: {{ $user->faceBiometric->registered_at->format('d/m/Y H:i') }}
                        </small>
                    @endif
                </div>

                <div class="container-capture-face">
                    <video id="faceVideo" autoplay muted playsinline style="object-fit: cover;"></video>
                    <canvas id="faceCanvas" class="position-absolute top-0 start-0"></canvas>
                </div>

                <div class="face-actions">
                    <button type="button" id="btnStartCamera" class="btn btn-wa btn-wa-primary">
                        Activar cámara
                    </button>

                    <button type="button" id="btnCaptureFace" class="btn btn-wa btn-wa-secondary" disabled>
                        Capturar rostro
                    </button>

                    <button type="button" id="btnSaveFace" class="btn btn-wa btn-wa-dark" disabled>
                        Guardar rostro
                    </button>

                    <button
                        type="button"
                        id="btnRemoveFace"
                        class="btn btn-wa btn-wa-danger"
                        {{ !($user->faceBiometric && $user->faceBiometric->is_enabled) ? 'disabled' : '' }}>
                        Eliminar registro
                    </button>
                </div>

                <div class="face-tips text-center">
                    <p class="mb-1">Mira al frente, evita sombras fuertes y asegúrate de que solo haya un rostro en cámara.</p>
                    <small>Este registro se usará para validar tu acceso de forma más rápida y segura.</small>
                </div>

                <div id="faceStatus" class="face-status">
                    Presiona <strong>Activar cámara</strong> para comenzar.
                </div>
            </div>

            <div class="face-footer">
                Los datos biométricos se resguardan con seguridad.
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
    FaceBio.initEnrollment({
        videoId: 'faceVideo',
        canvasId: 'faceCanvas',
        statusId: 'faceStatus',
        startButtonId: 'btnStartCamera',
        captureButtonId: 'btnCaptureFace',
        saveButtonId: 'btnSaveFace',
        removeButtonId: 'btnRemoveFace',
        modelsUrl: @json('https://cdn.jsdelivr.net/gh/justadudewhohacks/face-api.js/weights'),
        saveUrl: @json(route('settingfaces.savefaceprofile')),
        removeUrl: @json(route('settingfaces.removefaceprofile'))
    });
});
</script>
@endpush