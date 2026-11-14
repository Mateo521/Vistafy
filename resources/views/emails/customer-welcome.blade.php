<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a f33</title>
</head>

<body style="margin: 0; padding: 0; background-color: #F8F9FA; -webkit-font-smoothing: antialiased;">
    
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F8F9FA; width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 50px 20px;">
                

                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #f1f5f9; border-radius: 2px; border-collapse: separate; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">


                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center; background-color: #ffffff;">
                            <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #E30613; font-size: 16px; font-weight: 900; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 24px;">
                                F33
                            </div>
                            <h1 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0f172a; margin: 0 0 15px 0; font-size: 28px; letter-spacing: -0.5px; font-weight: 800;">
                                ¡Bienvenido a la plataforma!
                            </h1>
                            <div style="display: inline-block; background-color: #f8fafc; border: 1px solid #e2e8f0; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; padding: 6px 12px; border-radius: 2px;">
                                Acceso Autorizado
                            </div> 
                        </td>
                    </tr>


                    <tr>
                        <td style="padding: 20px 40px 40px 40px;">
                            
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #334155; font-size: 15px; line-height: 1.6; margin: 0 0 10px 0;">
                                Hola, <strong style="color: #0f172a;">{{ $user->name }}</strong>.
                            </p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #64748b; font-size: 15px; line-height: 1.6; margin: 0 0 35px 0;">
                                Tus credenciales fueron validadas correctamente. Ya tenés autorización para operar en la plataforma de fotógrafos.
                            </p>


                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 2px; margin: 0 0 40px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0 0 20px 0; color: #94a3b8; font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 1.5px; border-bottom: 1px solid #f1f5f9; padding-bottom: 12px;">
                                            Información de la cuenta
                                        </h3>

                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #64748b; font-size: 13px;">ID de Usuario / Email:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #0f172a; font-weight: bold; font-size: 13px;">
                                                    {{ $user->email }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>


                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 35px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('home') }}" style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 16px 32px; border-radius: 2px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; transition: background-color 0.3s;">
                                            Ingresar a la plataforma
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align: center; color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin-top: 0; margin-bottom: 35px;">
                                Si el botón no funciona, copiá y pegá este enlace:<br>
                                <a href="{{ route('home') }}" style="color: #E30613; text-decoration: none; font-weight: 500; margin-top: 5px; display: inline-block;">{{ route('home') }}</a>
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border-top: 1px solid #f1f5f9; margin: 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px; text-align: center;">
                                        <p style="margin: 0; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; line-height: 1.6;">
                                            <strong style="color: #0f172a; font-size: 10px; text-transform: uppercase; letter-spacing: 1px;">¿Necesitás ayuda técnica?</strong><br>
                                            <span style="margin-top: 4px; display: block;">Escribinos a <a href="mailto:contacto@f33.click" style="color: #E30613; text-decoration: none; font-weight: bold;">contacto@f33.click</a></span>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                <table width="600" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                    <tr>
                        <td style="padding: 30px 20px; text-align: center;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #94a3b8; font-size: 10px; margin: 0 0 12px 0; line-height: 1.5; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                Correo generado automáticamente. Por favor no respondas.
                            </p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #cbd5e1; font-size: 11px; margin: 0;">
                                © {{ date('Y') }} F33. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>