<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credenciales de acceso</title>
</head>
<body style="margin:0; padding:0; background-color:#f3f4f6; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">

    <div style="display:none; max-height:0; overflow:hidden; opacity:0; mso-hide:all;">
        Tus credenciales de acceso a WhatsApp-Sistemas ya están listas.
    </div>

    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color:#f3f4f6; margin:0; padding:0;">
        <tr>
            <td align="center" style="padding:24px 12px;">

                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="620" style="width:620px; max-width:620px;">
                    <tr>
                        <td style="background-color:#14a37f; height:56px; border-radius:24px 24px 0 0;"></td>
                    </tr>
                    <tr>
                        <td style="background-color:#ffffff; border-radius:0 0 24px 24px; padding:0 32px 32px 32px; border:1px solid #e5e7eb;">

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding-top:28px; padding-bottom:8px;">
                                        <div style="display:inline-block; background-color:#ecfdf5; color:#14a37f; font-size:28px; line-height:28px; padding:14px 16px; border-radius:18px;">
                                            💬
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top:6px;">
                                        <div style="font-size:28px; font-weight:700; color:#0f766e; line-height:1.2;">
                                            WhatsApp-Sistemas
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top:8px; font-size:14px; color:#6b7280; line-height:1.5;">
                                        Tu cuenta ha sido creada correctamente
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:28px;">
                                <tr>
                                    <td style="font-size:16px; line-height:1.8; color:#374151;">
                                        Hola, <strong>{{ $user->name }}</strong>.
                                        <br><br>
                                        Te compartimos tus credenciales de acceso para iniciar sesión en la plataforma.
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:28px;">
                                <tr>
                                    <td style="font-size:14px; font-weight:700; color:#4b5563; padding-bottom:8px;">
                                        Correo electrónico
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color:#f6fffa; border:1px solid #86d993; border-radius:10px; padding:16px 18px; font-size:16px; color:#1f2937; line-height:1.5;">
                                        {{ $user->email }}
                                    </td>
                                </tr>

                                <tr>
                                    <td style="font-size:14px; font-weight:700; color:#4b5563; padding-top:20px; padding-bottom:8px;">
                                        Contraseña temporal
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background-color:#f9fafb; border:1px solid #d1d5db; border-radius:10px; padding:16px 18px; font-size:24px; font-weight:700; color:#111827; letter-spacing:1px; line-height:1.3;">
                                        {{ $plainPassword }}
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:30px;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ url('/login') }}"
                                           style="background-color:#1f8f55; color:#ffffff; text-decoration:none; display:inline-block; padding:15px 32px; border-radius:10px; font-size:16px; font-weight:700;">
                                            Iniciar sesión
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:26px;">
                                <tr>
                                    <td style="background-color:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; padding:14px 16px; font-size:13px; color:#6b7280; line-height:1.7; text-align:center;">
                                        <strong style="color:#374151;">Importante:</strong>
                                        esta contraseña es temporal. Por seguridad, cámbiala después de iniciar sesión.
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top:28px;">
                                <tr>
                                    <td style="border-top:1px solid #e5e7eb; padding-top:18px; text-align:center; font-size:12px; color:#9ca3af; line-height:1.7;">
                                        Este correo fue enviado automáticamente por
                                        <strong style="color:#0f766e;">WhatsApp-Sistemas</strong>.
                                        <br>
                                        Equipo TechNova
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>

</body>
</html>