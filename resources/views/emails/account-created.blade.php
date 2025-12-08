<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Cuenta Creada - EMPRESA</title>
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; margin: 0; padding: 0; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden;">
                    
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #0f172a; padding: 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: bold;">
                                ¡Bienvenido a EMPRESA!
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #334155; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Tu cuenta ha sido creada exitosamente después de tu compra.
                            </p>

                            <div style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 20px; margin: 20px 0;">
                                <p style="margin: 0 0 10px 0; color: #475569; font-size: 14px; font-weight: bold;">
                                    TUS CREDENCIALES DE ACCESO:
                                </p>
                                <p style="margin: 0; color: #0f172a; font-size: 16px;">
                                    <strong>Email:</strong> {{ $email }}<br>
                                    <strong>Contraseña temporal:</strong> <code style="background: #e2e8f0; padding: 4px 8px; border-radius: 4px;">{{ $password }}</code>
                                </p>
                            </div>

                            <p style="color: #334155; font-size: 14px; line-height: 1.6; margin: 20px 0;">
                                Por favor, cambia tu contraseña después de iniciar sesión por primera vez.
                            </p>

                            <div style="text-align: center; margin: 30px 0;">
                                <a href="{{ route('login') }}" style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 14px 32px; border-radius: 4px; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Iniciar Sesión
                                </a>
                            </div>

                            <p style="color: #94a3b8; font-size: 12px; line-height: 1.6; margin: 20px 0 0 0;">
                                Ahora puedes acceder a tu historial de compras y descargar tus fotos en cualquier momento.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="color: #94a3b8; font-size: 12px; margin: 0;">
                                © {{ date('Y') }} EMPRESA Photography. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
