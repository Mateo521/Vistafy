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
            <td align="center" style="padding: 40px 20px;">
                
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; border-collapse: separate; overflow: hidden; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

                    <tr>
                        <td style="padding: 40px 30px; text-align: center; border-bottom: 1px solid #f1f5f9; background-color: #ffffff;">
                            <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #E30613; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                                F33
                            </div>
                            <h1 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0f172a; margin: 0 0 15px 0; font-size: 32px; letter-spacing: -0.5px; font-weight: 800;">
                                ¡Bienvenido!
                            </h1>
                            <div style="display: inline-block; background-color: #fef2f2; border: 1px solid #fee2e2; color: #E30613; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: bold; padding: 6px 16px; border-radius: 50px;">
                                Acceso autorizado
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px 30px;">
                            
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #334155; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;">
                                Hola, <strong style="color: #0f172a;">{{ $user->name }}</strong>.
                            </p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #64748b; font-size: 15px; line-height: 1.6; margin: 0 0 32px 0;">
                                Tus credenciales fueron validadas. Ya tenés autorización para operar en la plataforma.
                            </p>

                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 4px; margin: 0 0 40px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0 0 20px 0; color: #94a3b8; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                                            Credenciales de tu cuenta
                                        </h3>

                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #64748b; font-weight: 500;">ID:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #0f172a; font-weight: bold;">
                                                    {{ $user->email }}
                                                </td>
                                            </tr>
                                            <!--tr>
                                                <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Nivel de Acceso:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #0f172a; font-weight: bold;">
                                                    Cliente
                                                </td>
                                            </tr-->
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 20px 0 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('home') }}" style="display: inline-block; background-color: #000000; color: #ffffff; text-decoration: none; padding: 18px 36px; border-radius: 50px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: bold; font-size: 14px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            Ingresar a la plataforma
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="text-align: center; color: #94a3b8; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; margin-top: -20px; margin-bottom: 30px;">
                                Si el botón no funciona, podés ingresar manualmente a:<br>
                                <a href="{{ route('home') }}" style="color: #E30613; text-decoration: none;">{{ route('home') }}</a>
                            </p>
                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border-radius: 4px; margin: 30px 0 0 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px; text-align: center;">
                                        <p style="margin: 0; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6;">
                                            <strong style="color: #334155;">¿Necesitás ayuda técnica?</strong><br>
                                            Si tenés inconvenientes para acceder, escribinos a: <br>
                                            <a href="mailto:contacto@f33.click" style="color: #E30613; text-decoration: none; font-weight: bold;">contacto@f33.click</a>
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
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #94a3b8; font-size: 11px; margin: 0 0 10px 0; line-height: 1.5; font-weight: bold;">
                                CORREO GENERADO AUTOMÁTICAMENTE. POR FAVOR NO RESPONDAS A ESTE CORREO.
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