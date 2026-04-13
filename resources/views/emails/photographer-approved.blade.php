<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cuenta Aprobada - f33</title>
    <style>
        body { margin: 0; padding: 0; background-color: #111920; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; background-color: #111920; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1B2632; border: 1px solid #2a3b4c; border-top: 4px solid #FFB162; padding: 40px; }
        .header { text-align: center; margin-bottom: 35px; border-bottom: 1px solid #2a3b4c; padding-bottom: 20px; }
        .logo { font-family: Georgia, serif; font-size: 32px; font-style: italic; color: #EEE9DF; text-decoration: none; letter-spacing: 1px; }
        .title { font-family: Georgia, serif; font-size: 26px; color: #FFB162; margin-top: 0; font-weight: normal; text-align: center; }
        p { color: #C9C1B1; font-size: 15px; line-height: 1.6; margin-bottom: 20px; }
        .highlight { color: #EEE9DF; font-weight: bold; }
        .btn-container { text-align: center; margin: 40px 0; }
        .button { display: inline-block; background-color: #FFB162; color: #1B2632 !important; text-decoration: none; padding: 16px 32px; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; border-radius: 2px; }
        .steps { background-color: #111920; padding: 25px; margin: 30px 0; }
        .steps-title { color: #EEE9DF; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; margin-top: 0; margin-bottom: 15px; border-bottom: 1px solid #2a3b4c; padding-bottom: 10px; }
        .step-item { margin-bottom: 10px; color: #C9C1B1; font-size: 14px; }
        .footer { text-align: center; margin-top: 40px; padding-top: 20px; border-top: 1px solid #2a3b4c; }
        .footer p { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <a href="{{ config('app.url') }}" class="logo">f33</a>
            </div>
            
            <h1 class="title">¡Tu cuenta ha sido aprobada!</h1>
            
            <p>¡Excelentes noticias, <span class="highlight">{{ $photographer->business_name }}</span>! Tu perfil profesional ha superado nuestro proceso de revisión y ya formas parte de nuestra exclusiva red de fotógrafos.</p>
            
            <div class="steps">
                <p class="steps-title">Tus próximos pasos:</p>
                <div class="step-item"><span style="color:#FFB162;">1.</span> Inicia sesión en tu nuevo Panel de Control.</div>
                <div class="step-item"><span style="color:#FFB162;">2.</span> Vincula tu cuenta de Mercado Pago para poder cobrar.</div>
                <div class="step-item"><span style="color:#FFB162;">3.</span> Sube tu primera galería y comienza a vender.</div>
            </div>
            
            <div class="btn-container">
                <a href="{{ route('login') }}" class="button">Ingresar a mi Panel</a>
            </div>
            
            <p>Estamos muy contentos de tenerte con nosotros. Si tienes alguna duda sobre cómo subir tus fotos o configurar tus cobros, no dudes en contactarnos.</p>
            
            <p>¡Mucho éxito en tus ventas!<br><span class="highlight">El equipo de f33</span></p>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} f33. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>