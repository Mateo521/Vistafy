<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud recibida - F33</title>
</head>
<body style="margin: 0; padding: 0; background-color: #F2F0EB; font-family: Arial, Helvetica, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F2F0EB; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                    

                    <tr>
                        <td style="padding: 40px 40px 20px 40px; text-align: center;">
                            <img src="{{ asset('images/logo.png') }}" alt="F33" style="height: 40px; width: auto; display: block; margin: 0 auto;">
                            <p style="color: #9CA3AF; margin: 15px 0 0 0; font-size: 10px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase;">
                                Solicitud en revisión
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px 40px 10px 40px;">
                            <p style="color: #1F2937; font-size: 16px; margin: 0 0 15px 0;">
                                ¡Hola, <strong>{{ $user->name }}</strong>!
                            </p>
                            <p style="color: #4B5563; font-size: 15px; line-height: 1.6; margin: 0 0 30px 0;">
                                Recibimos correctamente tu solicitud para unirte a F33 como fotógrafo. Para mantener la calidad de nuestra plataforma, todos los perfiles nuevos pasan por un proceso de curaduría.
                            </p>


                            <div style="background-color: #FEF3C7; border: 1px solid #FDE68A; border-radius: 6px; padding: 25px; margin: 0 0 30px 0; text-align: center;">
                                <p style="margin: 0 0 5px 0; color: #D97706; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                                    Estado de tu cuenta
                                </p>
                                <p style="color: #B45309; font-size: 18px; font-weight: 900; margin: 0; text-transform: uppercase; letter-spacing: -0.5px;">
                                    EN REVISIÓN
                                </p>
                            </div>

                            <p style="color: #6B7280; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0; text-align: center;">
                                Nuestro equipo va a evaluar tu perfil y te vamos a enviar un correo de confirmación <strong>en un plazo máximo de 24 a 48 horas hábiles</strong>.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background-color: #F8F9FA; padding: 30px 40px; text-align: center; border-top: 1px solid #F3F4F6;">
                            <p style="color: #9CA3AF; font-size: 11px; margin: 0 0 10px 0;">
                                Correo automático de F33.
                            </p>
                            <p style="color: #D1D5DB; font-size: 10px; margin: 0; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
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