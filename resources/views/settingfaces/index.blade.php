@extends('layouts.app')

@section('title', 'Iniciar Sesión')

@section('content')
<link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/index-config-face-styles.css') }}">

<section class="container px-0 justify-content-center align-items-center container-main">
        <div class="card">
            <div class="card-header card-header-custom p-3">
                <img src="{{ asset('image/icons/ar_on_you.svg') }}" alt="settingFaces" width="32" class="white">
                <h4 class="text-white" style="margin-bottom:0px !important">
                    Configuración facial
                </h4>
            </div>
            <div class="card-body">
                <div class="container-photo">
                     <form method="POST" class="align-items-center justify-items-center d-flex justify-content-center" action="{{ route('register') }}">
                        <div class="container-capture-face">
                            <button class="btn-settings-faces">
                                 <img src="{{ asset('image/icons/photo_camera.svg') }}" alt="settingFaces" class="settingFaces white">
                            </button>
                        </div>
                        
                     </form>
                </div>
                <div class="container-fluid mt-2 ">
                    <p class="text-mute text-center">Sigue las indicación del registro biometrico</p>
                </div>
            <div class="card-footer text-center text-black">Los datos son resguardados con seguridad.</div>
        </div>
    </div>

</section>

@endsection

@push('scripts')

@endpush