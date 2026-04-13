<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud Recibida - f33</title>
    <style>
        body { margin: 0; padding: 0; background-color: #111920; font-family: Arial, sans-serif; -webkit-font-smoothing: antialiased; }
        .wrapper { width: 100%; background-color: #111920; padding: 40px 0; }
        .container { max-width: 600px; margin: 0 auto; background-color: #1B2632; border: 1px solid #2a3b4c; border-top: 4px solid #FFB162; padding: 40px; }
        .header { text-align: center; margin-bottom: 35px; border-bottom: 1px solid #2a3b4c; padding-bottom: 20px; }
        .logo { font-family: Georgia, serif; font-size: 32px; font-style: italic; color: #EEE9DF; text-decoration: none; letter-spacing: 1px; }
        .title { font-family: Georgia, serif; font-size: 24px; color: #FFB162; margin-top: 0; font-weight: normal; }
        p { color: #C9C1B1; font-size: 15px; line-height: 1.6; margin-bottom: 20px; }
        .highlight { color: #EEE9DF; font-weight: bold; }
        .status-box { background-color: #111920; border-left: 4px solid #FFB162; padding: 20px; margin: 30px 0; }
        .status-title { color: #FFB162; font-size: 11px; text-transform: uppercase; letter-spacing: 2px; font-weight: bold; margin: 0 0 10px 0; }
        .status-desc { color: #EEE9DF; font-size: 14px; margin: 0; }
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
            
            <h1 class="title">¡Hola, <span class="highlight">{{ $user->name }}</span>!</h1>
            
            <p>Hemos recibido tu solicitud para unirte como fotógrafo profesional en f33. Nos emociona mucho tu interés en formar parte de nuestra galería.</p>
            
            <div class="status-box">
                <p class="status-title">Estado de tu cuenta</p>
                <p class="status-desc">En Revisión Administrativa</p>
            </div>
            
            <p>Para mantener la exclusividad y calidad de nuestra plataforma, nuestro equipo está revisando tus datos. Este proceso suele demorar entre 24 y 48 horas.</p>
            
            <p>Te enviaremos un nuevo correo electrónico tan pronto como tu cuenta sea aprobada y esté lista para operar.</p>
            
            <p>¡Gracias por elegirnos!<br><span class="highlight">El equipo de f33</span></p>
            
            <div class="footer">
                <p>&copy; {{ date('Y') }} f33. Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>