<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp-Chris - @yield('title', 'Chat')</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --whatsapp-green: #25D366;
            --whatsapp-dark-green: #128C7E;
            --whatsapp-teal: #075E54;
            --light-green: #DCF8C6;
            --light-gray: #f0f2f5;
            --chat-bg: #efeae2;
            --header-bg: #f0f2f5;
            --border-color: #e9edef;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(180deg, #00a884 0%, #00a884 127px, #e5e5e5 127px, #e5e5e5 100%);
            height: 100vh;
            margin: 0;
            overflow: hidden;
        }

        .app-container {
            height: 100vh;
            padding: 15px 20px;
        }

        .chat-wrapper {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.1);
            height: calc(100vh - 30px);
            overflow: hidden;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: 30%;
            background-color: #fff;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
        }

        /* Chat Area */
        .chat-area {
            width: 70%;
            background-color: var(--chat-bg);
            display: flex;
            flex-direction: column;
        }

        /* Header */
        .chat-header, .sidebar-header {
            background-color: var(--header-bg);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        /* Avatar */
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }

        .avatar-lg {
            width: 50px;
            height: 50px;
        }

        .avatar-sm {
            width: 30px;
            height: 30px;
        }

        /* Online Indicator */
        .online-indicator {
            width: 10px;
            height: 10px;
            background-color: #31a24c;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 2px;
            right: 2px;
        }

        /* Contact Item */
        .contact-item {
            padding: 12px 16px;
            cursor: pointer;
            transition: background 0.2s;
            border-bottom: 1px solid var(--border-color);
        }

        .contact-item:hover {
            background-color: #f5f6f6;
        }

        .contact-item.active {
            background-color: #f0f2f5;
        }

        /* Messages Container */
        .messages-container {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 32 32" opacity="0.05"><circle fill="%23333" cx="16" cy="16" r="2"/></svg>');
        }

        /* Message Bubbles */
        .message {
            display: flex;
            margin-bottom: 12px;
            animation: fadeIn 0.3s ease;
        }

        .message.sent {
            justify-content: flex-end;
        }

        .message.received {
            justify-content: flex-start;
        }

        .message-content {
            max-width: 65%;
            padding: 8px 12px;
            border-radius: 7.5px;
            position: relative;
            word-wrap: break-word;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        .message.sent .message-content {
            background-color: #d9fdd3;
            border-top-right-radius: 0;
        }

        .message.received .message-content {
            background-color: white;
            border-top-left-radius: 0;
        }

        .message-time {
            font-size: 11px;
            color: #667781;
            margin-top: 4px;
            text-align: right;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 4px;
        }

        .message-status {
            font-size: 12px;
            color: #53bdeb;
        }

        /* Message Input */
        .message-input-area {
            background-color: var(--header-bg);
            padding: 10px 16px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message-input {
            flex: 1;
            border: none;
            border-radius: 20px;
            padding: 10px 15px;
            outline: none;
            background-color: white;
        }

        .message-input:focus {
            box-shadow: 0 0 0 2px rgba(37, 211, 102, 0.2);
        }

        /* Buttons */
        .btn-icon {
            background: none;
            border: none;
            color: #54656f;
            font-size: 20px;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background 0.2s;
        }

        .btn-icon:hover {
            background-color: rgba(0,0,0,0.05);
        }

        .btn-icon:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .btn-send {
            color: var(--whatsapp-green);
        }

        .btn-send:hover {
            color: var(--whatsapp-dark-green);
        }

        /* Search */
        .search-box {
            padding: 8px 12px;
            background-color: white;
            border-bottom: 1px solid var(--border-color);
        }

        .search-input {
            width: 100%;
            padding: 8px 12px;
            border: none;
            border-radius: 20px;
            background-color: #f0f2f5;
            outline: none;
        }

        .search-input:focus {
            background-color: #e5e5e5;
        }

        /* File Preview */
        .file-preview {
            max-width: 200px;
            max-height: 200px;
            border-radius: 7.5px;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .file-preview.video {
            max-width: 250px;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #c0c0c0;
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #a0a0a0;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes typing {
            0% { opacity: 0.3; }
            50% { opacity: 1; }
            100% { opacity: 0.3; }
        }

        .typing-indicator {
            display: flex;
            gap: 4px;
            padding: 10px;
            background-color: white;
            border-radius: 18px;
            width: fit-content;
        }

        .typing-dot {
            width: 8px;
            height: 8px;
            background-color: #8696a0;
            border-radius: 50%;
            animation: typing 1.4s infinite;
        }

        .typing-dot:nth-child(2) { animation-delay: 0.2s; }
        .typing-dot:nth-child(3) { animation-delay: 0.4s; }

        /* Responsive */
        @media (max-width: 768px) {
            .app-container {
                padding: 0;
            }
            
            .chat-wrapper {
                border-radius: 0;
                height: 100vh;
            }
            
            .sidebar {
                width: 100%;
                display: {{ isset($otherUser) ? 'none' : 'flex' }};
            }
            
            .chat-area {
                width: {{ isset($otherUser) ? '100%' : '0' }};
                display: {{ isset($otherUser) ? 'flex' : 'none' }};
            }
        }

        /* Loading Spinner */
        .spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid var(--whatsapp-green);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Empty State */
        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            color: #8696a0;
            text-align: center;
            padding: 20px;
        }

        .empty-state i {
            font-size: 60px;
            margin-bottom: 20px;
            color: #d1d7db;
        }

        /* Toast Notifications */
        .toast-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .toast {
            background-color: white;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease;
        }

        .toast.success { border-left: 4px solid #25D366; }
        .toast.error { border-left: 4px solid #f44336; }
        .toast.warning { border-left: 4px solid #ff9800; }
        .toast.info { border-left: 4px solid #2196f3; }

        @keyframes slideIn {
            from { transform: translateX(100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
    </style>
    
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