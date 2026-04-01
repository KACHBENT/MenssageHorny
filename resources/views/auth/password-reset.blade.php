<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperación de contraseña</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background:#f5f6f8; padding:30px; color:#1f2937;">
    <div style="max-width:600px; margin:0 auto; background:#ffffff; border-radius:12px; padding:30px; border:1px solid #e5e7eb;">
        <h2 style="margin-top:0; color:#00a884;">Recuperación de contraseña</h2>

        <p>Hola {{ $user->name }},</p>

        <p>Recibimos una solicitud para restablecer tu contraseña en <strong>WhatsApp-Sistemas</strong>.</p>

        <p style="margin:30px 0;">
            <a href="{{ $resetUrl }}"
               style="background:#00a884; color:#ffffff; text-decoration:none; padding:14px 20px; border-radius:8px; display:inline-block;">
                Restablecer contraseña
            </a>
        </p>

        <p>Este enlace expira en <strong>60 minutos</strong>.</p>

        <p>Si tú no hiciste esta solicitud, puedes ignorar este correo.</p>

        <hr style="border:none; border-top:1px solid #e5e7eb; margin:25px 0;">

        <small style="color:#6b7280;">
            Si el botón no funciona, copia y pega este enlace en tu navegador:<br>
            {{ $resetUrl }}
        </small>
    </div>
</body>
</html>