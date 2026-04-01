@extends('layouts.app')

@section('title', 'Configuración biométrica')

@section('content')
<link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
<link rel="stylesheet" href="{{ asset('css/index-config-face-styles.css') }}">

<meta name="csrf-token" content="{{ csrf_token() }}">

<div id="biometric-settings-config"
     data-enroll-start-url="{{ route('biometric.enroll.start') }}"
     data-enroll-finish-url="{{ route('biometric.enroll.finish') }}">
</div>

<section class="container px-0 justify-content-center align-items-center container-main">
    <div class="card">
        <div class="card-header card-header-custom p-3">
            <h4 class="text-white mb-0">Configuración de huella</h4>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="text-center mb-4">
                <button type="button"
                        id="btnRegisterFingerprint"
                        class="button-face-print btn btn-success rounded-circle border-0"
                        style="width:90px;height:90px;">
                    <img src="{{ asset('image/icons/fingerprint.svg') }}"
                         class="icon-finger-print"
                         style="width:42px;height:42px;"
                         alt="Huella">
                </button>
                <p class="mt-2 mb-0">Registrar huella en este dispositivo</p>
            </div>

            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Dispositivo</th>
                            <th>Alias</th>
                            <th>Estado</th>
                            <th>Último uso</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($credentials as $credential)
                            <tr>
                                <td>{{ $credential->device_name ?? $credential->device_id }}</td>
                                <td>{{ $credential->key_alias }}</td>
                                <td>{{ $credential->enabled ? 'Activa' : 'Inactiva' }}</td>
                                <td>{{ optional($credential->last_used_at)->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    @if($credential->enabled)
                                        <form method="POST" action="{{ route('biometric.disable', $credential) }}">
                                            @csrf
                                            <button class="btn btn-sm btn-outline-danger">Deshabilitar</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay huellas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const configEl = document.getElementById('biometric-settings-config');

    const enrollStartUrl = configEl.dataset.enrollStartUrl;
    const enrollFinishUrl = configEl.dataset.enrollFinishUrl;

    const btnRegisterFingerprint = document.getElementById('btnRegisterFingerprint');

    if (!btnRegisterFingerprint) return;

    function getDeviceId() {
        if (!window.AndroidBiometric) {
            throw new Error('La app Android no está disponible.');
        }
        return window.AndroidBiometric.getDeviceId();
    }

    function getDeviceName() {
        if (!window.AndroidBiometric) {
            return 'Android';
        }
        return window.AndroidBiometric.getDeviceName();
    }

    btnRegisterFingerprint.addEventListener('click', async function () {
        try {
            if (!window.AndroidBiometric) {
                throw new Error('La autenticación biométrica solo funciona dentro de la app Android.');
            }

            const deviceId = getDeviceId();
            const deviceName = getDeviceName();

            const start = await fetch(enrollStartUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    device_id: deviceId,
                    device_name: deviceName
                })
            });

            const json = await start.json();

            if (!start.ok) {
                throw new Error(json.message || 'No se pudo iniciar el registro');
            }

            window.AndroidBiometric.enrollFingerprint(JSON.stringify({
                challenge_id: json.challenge_id,
                nonce: json.nonce
            }));

        } catch (e) {
            alert(e.message);
        }
    });

    window.addEventListener('biometric-enroll-result', async function (event) {
        try {
            const detail = event.detail;

            if (!detail.success) {
                throw new Error(detail.message || 'No se pudo registrar la huella');
            }

            const finish = await fetch(enrollFinishUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json'
                },
                credentials: 'same-origin',
                body: JSON.stringify(detail.payload)
            });

            const json = await finish.json();

            if (!finish.ok) {
                throw new Error(json.message || 'No se pudo terminar el registro');
            }

            alert(json.message);
            location.reload();

        } catch (e) {
            alert(e.message);
        }
    });
});
</script>
@endpush