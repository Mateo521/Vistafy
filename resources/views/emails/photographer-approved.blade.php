<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso concedido - F33</title>
</head>

<body
    style="margin: 0; padding: 0; background-color: #F2F0EB; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F2F0EB; padding: 40px 20px;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">


                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                        
                            <img src="{{ asset('images/logo.png') }}" alt="F33" style="height: 40px; width: auto; display: block; margin: 0 auto;">


                            <p
                                style="color: #9CA3AF; margin: 10px 0 0 0; font-size: 10px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">
                                Autorización aprobada
                            </p>
                        </td>
                    </tr>


                    <tr>
                        <td style="padding: 20px 40px 10px 40px;">
                            <p style="color: #1F2937; font-size: 16px; margin: 0 0 15px 0;">
                                ¡Hola, <strong>{{ $photographer->business_name }}</strong>!
                            </p>
                            <p style="color: #4B5563; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;">
                                Tu perfil ha superado satisfactoriamente nuestra curaduría técnica y estética.
                                <strong>Tu cuenta de fotógrafo ya está activa y lista para operar.</strong>
                            </p>


                            <div
                                style="background-color: #F8F9FA; border: 1px solid #F3F4F6; border-radius: 6px; padding: 25px; margin: 0 0 30px 0;">
                                <p
                                    style="margin: 0 0 15px 0; color: #9CA3AF; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                    Próximos pasos
                                </p>

                                <table width="100%" cellpadding="0" cellspacing="0"
                                    style="color: #374151; font-size: 14px; line-height: 1.6;">
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">01.</td>
                                        <td style="padding-bottom: 12px;"><strong>Accedé al panel</strong> de control
                                            profesional.</td>
                                    </tr>
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">02.</td>
                                        <td style="padding-bottom: 12px;"><strong>Vinculá Mercado Pago</strong> para
                                            recibir tus ganancias automáticamente.</td>
                                    </tr>
                                    <tr>
                                        <td width="30" valign="top" style="color: #E30613; font-weight: bold;">03.</td>
                                        <td><strong>Creá tu primer evento</strong> y comenzá a subir fotos.</td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ route('login') }}"
                                    style="display: inline-block; background-color: #000000; color: #ffffff; text-decoration: none; padding: 16px 45px; border-radius: 50px; font-weight: bold; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">
                                    Ingresar a mi cuenta
                                </a>
                            </div>

                            <p
                                style="color: #6B7280; font-size: 13px; line-height: 1.5; margin: 0 0 20px 0; text-align: center;">
                                Si experimentás problemas al ingresar, por favor contactá a nuestro soporte.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background-color: #F8F9FA; padding: 30px 40px; text-align: center; border-top: 1px solid #F3F4F6;">
                            <p style="color: #9CA3AF; font-size: 11px; margin: 0 0 15px 0;">
                                Si el botón superior no funciona, copiá y pegá este enlace:<br>
                                <a href="{{ route('login') }}"
                                    style="color: #E30613; text-decoration: none; line-height: 2;">{{ route('login') }}</a>
                            </p>
                            <p
                                style="color: #D1D5DB; font-size: 10px; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                © {{ date('Y') }} F33. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
                </div>
            </td>
        </tr>
    </table>
</body>

</html>