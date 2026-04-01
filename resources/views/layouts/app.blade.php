<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
<<<<<<< HEAD
=======
    <link rel="shortcut icon" href="{{ asset('favicon.ico')}}" />
   
>>>>>>> main
    <title>WhatsApp-Sistemas - @yield('title', 'Chat')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <!-- Estilos de base de css -->
    <link rel="stylesheet" href="{{ asset('css/index.styles.css') }}">
    @stack('styles')
    
</head>
<body>
    <div class="app-container">
        <div class="chat-wrapper">
            @yield('content')
        </div>
    </div>

    <!-- Toast Container -->
    <div class="toast-container" id="toastContainer"></div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Configuración global de AJAX
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Función para mostrar notificaciones
        function showToast(message, type = 'info', duration = 3000) {
            const toast = $(`
                <div class="toast ${type}">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 
                                     type === 'error' ? 'exclamation-circle' : 
                                     type === 'warning' ? 'exclamation-triangle' : 
                                     'info-circle'}"></i>
                    <span>${message}</span>
                </div>
            `);
            
            $('#toastContainer').append(toast);
            
            setTimeout(() => {
                toast.fadeOut(300, function() {
                    $(this).remove();
                });
            }, duration);
        }

        // Función para formatear fechas
        function formatTime(date) {
            const d = new Date(date);
            let hours = d.getHours();
            let minutes = d.getMinutes();
            const ampm = hours >= 12 ? 'PM' : 'AM';
            hours = hours % 12;
            hours = hours ? hours : 12;
            minutes = minutes < 10 ? '0' + minutes : minutes;
            return hours + ':' + minutes + ' ' + ampm;
        }

        // Función para manejar errores
        function handleError(error) {
            console.error('Error:', error);
            let message = 'Ha ocurrido un error';
            
            if (error.responseJSON && error.responseJSON.message) {
                message = error.responseJSON.message;
            } else if (error.status === 422) {
                message = 'Datos inválidos';
            } else if (error.status === 401) {
                message = 'No autorizado';
                window.location.href = '/login';
            }
            
            showToast(message, 'error');
        }

        // Mantener sesión activa
        setInterval(() => {
            fetch('/sanctum/csrf-cookie');
        }, 600000); // Cada 10 minutos
    </script>
    
    @stack('scripts')
</body>
</html>