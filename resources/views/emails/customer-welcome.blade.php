<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bienvenido a f33 - f33</title>
</head>
<body style="font-family: 'Courier New', Courier, monospace; margin: 0; padding: 0; background-color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #000000; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #18181b; border: 1px solid #3f3f46; border-radius: 0px; overflow: hidden;">
                    
                    <tr>
                        <td style="background-color: #09090b; padding: 40px; text-align: center; border-bottom: 2px solid #dc2626;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 28px; font-weight: 900; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: -1px;">
                                F33 <span style="color: #dc2626;"></span>
                            </h1>
                            <p style="color: #71717a; margin: 0; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase;">
                                [ Lorem ipsum dolor sit amet ] 
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #e4e4e7; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0; text-transform: uppercase;">
                                SALUDOS <strong>{{ $user->name }}</strong>,
                            </p>
                            <p style="color: #a1a1aa; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0;">
                                Tus credenciales fueron validadas. Ya tenés autorización para operar en la plataforma y extraer material fotográfico.
                            </p>

                            <div style="background-color: #000000; border-left: 4px solid #dc2626; padding: 20px; margin: 0 0 30px 0;">
                                <h3 style="margin: 0 0 10px 0; color: #dc2626; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em;">
                                    [ Credenciales ]
                                </h3>
                                <p style="color: #a1a1aa; font-size: 13px; margin: 0 0 5px 0;">ID Operativo: <strong>{{ $user->email }}</strong></p>
                                <p style="color: #a1a1aa; font-size: 13px; margin: 0;">Nivel de Acceso: <strong>CLIENTE GENERAL</strong></p>
                            </div>

                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ route('home') }}" style="display: inline-block; background-color: #dc2626; color: #ffffff; text-decoration: none; padding: 16px 40px; font-weight: bold; font-size: 12px; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: 0.1em;">
                                    [ INGRESAR ]
                                </a>
                            </div>
                            
                            <p style="text-align: center; color: #52525b; font-size: 10px; margin-top: 20px;">
                                Si el enlace falla, accede manualmente a:<br>
                                <a href="{{ route('home') }}" style="color: #a1a1aa;">{{ route('home') }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #09090b; padding: 30px; text-align: center; border-top: 1px solid #27272a;">
                            <p style="color: #52525b; font-size: 11px; margin: 0 0 10px 0; line-height: 1.5; text-transform: uppercase;">
                                Correo automático de f33.click.
                            </p>
                            <p style="color: #3f3f46; font-size: 10px; margin: 0;">
                                © {{ date('Y') }} F33. DERECHOS GESTIONADOS.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>