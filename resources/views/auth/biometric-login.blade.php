@extends('layouts.app')

@section('title', 'Login biométrico')

@section('content')
<link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/index-config-face-styles.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="biometric-login-config"
     data-login-start-url="{{ route('biometric.login.start') }}"
     data-login-verify-url="{{ route('biometric.login.verify') }}">
</div>

<section class="container px-0 justify-content-center align-items-center container-main">
    <div class="card">
        <div class="card-header card-header-custom p-3 text-center">
            <h4 class="text-white mb-0">Acceder con huella</h4>
        </div>

        <div class="card-body text-center">
            <button type="button"
                    id="btnLoginFingerprint"
                    class="button-face-print btn btn-success rounded-circle border-0"
                    style="width:100px;height:100px;">
                <img src="{{ asset('image/icons/fingerprint.svg') }}"
                     alt="Huella"
                     class="icon-finger-print"
                     style="width:46px;height:46px;">
            </button>

            <p class="mt-3 mb-0">Usar huella registrada en este dispositivo</p>
        </div>

        <div class="card-footer text-center text-black">
            El dispositivo debe tener una huella previamente registrada en la app.
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const configEl = document.getElementById('biometric-login-config');

    const loginStartUrl = configEl.dataset.loginStartUrl;
    const loginVerifyUrl = configEl.dataset.loginVerifyUrl;

    const btnLoginFingerprint = document.getElementById('btnLoginFingerprint');

    if (!btnLoginFingerprint) return;

    btnLoginFingerprint.addEventListener('click', async function () {
        try {
            if (!window.AndroidBiometric) {
                throw new Error('La autenticación biométrica solo funciona dentro de la app Android.');
            }

            const deviceId = window.AndroidBiometric.getDeviceId();

            const start = await fetch(loginStartUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    device_id: deviceId
                })
            });

            const json = await start.json();

            if (!start.ok) {
                throw new Error(json.message || 'No se pudo iniciar el login');
            }

            window.AndroidBiometric.signLoginChallenge(JSON.stringify({
                challenge_id: json.challenge_id,
                nonce: json.nonce
            }));

        } catch (e) {
            alert(e.message);
        }
    });

    window.addEventListener('biometric-login-result', async function (event) {
        try {
            const detail = event.detail;

            if (!detail.success) {
                throw new Error(detail.message || 'Autenticación fallida');
            }

            const verify = await fetch(loginVerifyUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(detail.payload)
            });

            const json = await verify.json();

            if (!verify.ok) {
                throw new Error(json.message || 'No se pudo verificar el login');
            }

            window.location.href = json.redirect || '/';

        } catch (e) {
            alert(e.message);
        }
    });
});
</script>
@endpush