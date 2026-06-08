<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso concedido - F33</title>
</head>
<body style="font-family: 'Courier New', Courier, monospace; margin: 0; padding: 0; background-color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #000000; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #18181b; border: 1px solid #3f3f46; border-radius: 0px; overflow: hidden;">
                    
                    <tr>
                        <td style="background-color: #09090b; padding: 40px; text-align: center; border-bottom: 2px solid #E30613;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 28px; font-weight: 900; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: -1px;">
                                F33 <span style="color: #E30613;">.</span>
                            </h1>
                            <p style="color: #71717a; margin: 0; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase;">
                                [ Autorización ]
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #e4e4e7; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0; text-transform: uppercase;">
                                SALUDOS <strong>{{ $photographer->business_name }}</strong>,
                            </p>
                            <p style="color: #a1a1aa; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0;">
                                Tu perfil ha superado satisfactoriamente nuestra curaduría técnica y estética. El acceso fue habilitado.
                            </p>

                            <div style="background-color: #000000; border-left: 4px solid #E30613; padding: 20px; margin: 0 0 30px 0;">
                                <h3 style="margin: 0 0 15px 0; color: #E30613; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em;">
                                    [ Inicio ]
                                </h3>
                                
                                <table width="100%" cellpadding="0" cellspacing="0" style="color: #a1a1aa; font-size: 13px; line-height: 1.6;">
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">01.</td>
                                        <td padding-bottom="10">Accedé al panel.</td>
                                    </tr>
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">02.</td>
                                        <td padding-bottom="10">Vinculá Mercado Pago para recibir transferencias.</td>
                                    </tr>
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">03.</td>
                                        <td>Creá evento y subí fotos.</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ route('login') }}" style="display: inline-block; background-color: #E30613; color: #ffffff; text-decoration: none; padding: 16px 40px; font-weight: bold; font-size: 12px; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: 0.1em;">
                                    [ INICIAR ]
                                </a>
                            </div>
                            
                            <p style="color: #a1a1aa; font-size: 13px; line-height: 1.6; margin: 0 0 20px 0;">
                                Si experimentas problemas, contactá al soporte.
                            </p>

                            <p style="text-align: center; color: #52525b; font-size: 10px; margin-top: 30px;">
                                Si el enlace principal falla, accede mediante:<br>
                                <a href="{{ route('login') }}" style="color: #a1a1aa;">{{ route('login') }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #09090b; padding: 30px; text-align: center; border-top: 1px solid #27272a;">
                            <p style="color: #52525b; font-size: 11px; margin: 0 0 10px 0; line-height: 1.5; text-transform: uppercase;">
                                correo automático de F33.
                            </p>
                            <p style="color: #3f3f46; font-size: 10px; margin: 0;">
                                © {{ date('Y') }} F33. TODOS LOS DERECHOS RESERVADOS.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>