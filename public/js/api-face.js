(function () {
    let modelsLoaded = false;
    let activeStream = null;

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    function setStatus(statusId, message, type = 'light') {
        const el = document.getElementById(statusId);
        if (!el) return;

        el.className = `alert alert-${type} text-center mt-3 mb-0`;
        el.textContent = message;
    }

    async function loadModels(modelsUrl) {
        if (modelsLoaded) return;

        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelsUrl),
            faceapi.nets.faceLandmark68Net.loadFromUri(modelsUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelsUrl),
        ]);

        modelsLoaded = true;
    }

    async function startCamera(videoEl) {
        stopCamera();

        activeStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 }
            },
            audio: false
        });

        videoEl.srcObject = activeStream;
        await videoEl.play();
    }

    function stopCamera() {
        if (activeStream) {
            activeStream.getTracks().forEach(track => track.stop());
            activeStream = null;
        }
    }

    async function detectSingleFace(videoEl, canvasEl) {
        const detection = await faceapi
            .detectSingleFace(
                videoEl,
                new faceapi.TinyFaceDetectorOptions({
                    inputSize: 320,
                    scoreThreshold: 0.5
                })
            )
            .withFaceLandmarks()
            .withFaceDescriptor();

        if (!detection) {
            throw new Error('No se detectó un rostro. Intenta de nuevo.');
        }

        canvasEl.width = videoEl.videoWidth;
        canvasEl.height = videoEl.videoHeight;

        const dims = faceapi.matchDimensions(canvasEl, {
            width: videoEl.videoWidth,
            height: videoEl.videoHeight
        });

        const resized = faceapi.resizeResults(detection, dims);
        const ctx = canvasEl.getContext('2d');
        ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);

        faceapi.draw.drawDetections(canvasEl, [resized]);
        faceapi.draw.drawFaceLandmarks(canvasEl, [resized]);

        return Array.from(detection.descriptor);
    }

    async function postJson(url, payload) {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            },
            body: JSON.stringify(payload)
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Ocurrió un error en la petición.');
        }

        return data;
    }

    async function deleteJson(url) {
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Ocurrió un error al eliminar.');
        }

        return data;
    }

    async function initEnrollment(config) {
        const videoEl = document.getElementById(config.videoId);
        const canvasEl = document.getElementById(config.canvasId);
        const btnStart = document.getElementById(config.startButtonId);
        const btnCapture = document.getElementById(config.captureButtonId);
        const btnSave = document.getElementById(config.saveButtonId);
        const btnRemove = document.getElementById(config.removeButtonId);

        let lastDescriptor = null;

        btnStart?.addEventListener('click', async function () {
            try {
                setStatus(config.statusId, 'Cargando modelos faciales...', 'warning');
                await loadModels(config.modelsUrl);

                setStatus(config.statusId, 'Activando cámara...', 'warning');
                await startCamera(videoEl);

                btnCapture.disabled = false;
                setStatus(config.statusId, 'Cámara activa. Ahora captura tu rostro.', 'success');
            } catch (error) {
                console.error(error);
                setStatus(config.statusId, error.message || 'No se pudo activar la cámara.', 'danger');
            }
        });

        btnCapture?.addEventListener('click', async function () {
            try {
                setStatus(config.statusId, 'Analizando rostro...', 'warning');
                lastDescriptor = await detectSingleFace(videoEl, canvasEl);

                btnSave.disabled = false;
                setStatus(config.statusId, 'Rostro detectado correctamente. Ya puedes guardarlo.', 'success');
            } catch (error) {
                console.error(error);
                btnSave.disabled = true;
                setStatus(config.statusId, error.message || 'No se pudo capturar el rostro.', 'danger');
            }
        });

        btnSave?.addEventListener('click', async function () {
            try {
                if (!lastDescriptor) {
                    setStatus(config.statusId, 'Primero debes capturar un rostro.', 'danger');
                    return;
                }

                btnSave.disabled = true;
                setStatus(config.statusId, 'Guardando perfil facial...', 'warning');

                const result = await postJson(config.saveUrl, {
                    descriptor: lastDescriptor
                });

                if (btnRemove) btnRemove.disabled = false;
                setStatus(config.statusId, result.message || 'Perfil facial guardado.', 'success');
            } catch (error) {
                console.error(error);
                btnSave.disabled = false;
                setStatus(config.statusId, error.message || 'No se pudo guardar el rostro.', 'danger');
            }
        });

        btnRemove?.addEventListener('click', async function () {
            try {
                setStatus(config.statusId, 'Eliminando perfil facial...', 'warning');

                const result = await deleteJson(config.removeUrl);

                lastDescriptor = null;
                if (btnSave) btnSave.disabled = true;
                if (btnRemove) btnRemove.disabled = true;

                const ctx = canvasEl.getContext('2d');
                ctx.clearRect(0, 0, canvasEl.width, canvasEl.height);

                setStatus(config.statusId, result.message || 'Perfil facial eliminado.', 'success');
            } catch (error) {
                console.error(error);
                setStatus(config.statusId, error.message || 'No se pudo eliminar el perfil.', 'danger');
            }
        });
    }

    async function initLogin(config) {
        const videoEl = document.getElementById(config.videoId);
        const canvasEl = document.getElementById(config.canvasId);
        const btnStart = document.getElementById(config.startButtonId);
        const btnVerify = document.getElementById(config.verifyButtonId);
        const identityEl = document.getElementById(config.identityId);
        const rememberEl = document.getElementById(config.rememberId);

        btnStart?.addEventListener('click', async function () {
            try {
                setStatus(config.statusId, 'Cargando modelos faciales...', 'warning');
                await loadModels(config.modelsUrl);

                setStatus(config.statusId, 'Activando cámara...', 'warning');
                await startCamera(videoEl);

                btnVerify.disabled = false;
                setStatus(config.statusId, 'Cámara activa. Ya puedes iniciar sesión con tu rostro.', 'success');
            } catch (error) {
                console.error(error);
                setStatus(config.statusId, error.message || 'No se pudo activar la cámara.', 'danger');
            }
        });

        btnVerify?.addEventListener('click', async function () {
            try {
                btnVerify.disabled = true;
                setStatus(config.statusId, 'Verificando identidad facial...', 'warning');

                const descriptor = await detectSingleFace(videoEl, canvasEl);

                const result = await postJson(config.verifyUrl, {
                    descriptor: descriptor,
                    identity: identityEl ? identityEl.value.trim() : '',
                    remember: rememberEl ? rememberEl.checked : false
                });

                setStatus(config.statusId, result.message || 'Acceso concedido.', 'success');

                if (result.redirect) {
                    window.location.href = result.redirect;
                    return;
                }

                btnVerify.disabled = false;
            } catch (error) {
                console.error(error);
                btnVerify.disabled = false;
                setStatus(config.statusId, error.message || 'No fue posible iniciar sesión.', 'danger');
            }
        });
    }

    window.FaceBio = {
        initEnrollment,
        initLogin
    };

    window.addEventListener('beforeunload', stopCamera);
})();