# MenssageHorny

Aplicación web en **Laravel 12**.

## Configuración para Hostinger (producción)

Dominio base configurado: **https://kachbentsystem.online**

### 1) Requisitos

- PHP 8.2+
- MySQL
- Extensiones de Laravel (`mbstring`, `openssl`, `pdo`, `ctype`, `json`, `tokenizer`, `xml`, `fileinfo`)

### 2) Subida del proyecto

- Sube el proyecto a una carpeta, por ejemplo `~/menssagehorny`.
- Copia `.env.hostinger.example` a `.env` y completa credenciales.

### 3) Document root

Opción recomendada:
- Apunta el dominio a `~/menssagehorny/public`

Opción alternativa (si no puedes cambiar el document root):
- Mantén el dominio apuntando a la raíz del proyecto y usa el `.htaccess` raíz incluido para enrutar todo hacia `public/`.

### 4) Variables clave (`.env`)

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kachbentsystem.online
FILESYSTEM_DISK=public
SESSION_DOMAIN=.kachbentsystem.online

MAIL_MAILER=smtp
MAIL_SCHEME=tls
MAIL_HOST=smtp.hostinger.com
MAIL_PORT=587
MAIL_USERNAME=no-reply@kachbentsystem.online
MAIL_PASSWORD=TU_PASSWORD_SMTP
MAIL_FROM_ADDRESS=no-reply@kachbentsystem.online
MAIL_FROM_NAME="MenssageHorny"
```

### 5) Instalación y optimización

```bash
composer install --no-dev --optimize-autoloader
php artisan key:generate --force
php artisan migrate --force
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 6) Solución al error 403 en imágenes

Si ves `403 Forbidden` en avatares o archivos de chat:

1. Asegura symlink público:
   ```bash
   php artisan storage:link
   ```
2. Verifica que exista `public/storage` apuntando a `storage/app/public`.
3. Usa `FILESYSTEM_DISK=public` en `.env`.
4. Si usas opción alternativa de Document Root, usa el `.htaccess` raíz de este repo (ya está preparado para evitar bloqueos de `/storage/*`).
5. Permisos recomendados:
   - `storage/` y `bootstrap/cache/` con escritura para el usuario web.

### 7) Autenticación por correo (verificación)

Este proyecto ahora requiere correo verificado para entrar al chat:

- Al registrarse, se envía email de verificación.
- Si intenta login sin verificar, se bloquea acceso y se ofrece reenvío del correo.
- Las rutas del chat requieren middleware `verified`.

### 8) Prueba de correo SMTP Hostinger

```bash
php artisan tinker
```

Luego:

```php
\Illuminate\Support\Facades\Mail::raw('Prueba SMTP Hostinger OK', function ($m) {
    $m->to('tu-correo@dominio.com')->subject('Test MenssageHorny');
});
```

Si falla, revisa `MAIL_USERNAME`, `MAIL_PASSWORD`, DNS SPF/DKIM y que el buzón exista en Hostinger.
