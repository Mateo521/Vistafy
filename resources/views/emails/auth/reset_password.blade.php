<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #F8F9FA; color: #1e293b; padding: 20px; }
        .container { max-width: 500px; margin: 0 auto; background: #ffffff; padding: 40px 30px; border-radius: 4px;  text-align: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .logo { font-size: 24px; font-weight: bold; color: #000; margin-bottom: 20px; letter-spacing: 2px; }
        h2 { margin-top: 0; font-size: 22px; color: #000000; }
        p { color: #64748b; font-size: 14px; line-height: 1.6; margin-bottom: 30px; text-align: left; }
        .btn { display: inline-block; background-color: #000000; color: #ffffff; padding: 14px 28px; text-decoration: none; border-radius: 50px; font-weight: bold; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
        .btn:hover { background-color: #E30613; }
        .footer { margin-top: 40px; font-size: 11px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">F33</div>
        <h2>Recuperación de contraseña</h2>
        
        <p>Recibimos una solicitud para restablecer la contraseña de tu cuenta en F33. Si fuiste vos, hacé clic en el botón de abajo para elegir una nueva contraseña.</p>
        
        <a href="{{ $resetUrl }}" class="btn">Restablecer mi contraseña</a>
        
        <p style="margin-top: 30px;">Si no hiciste esta solicitud, podés ignorar este correo de forma segura.</p>
        
        <div class="footer">
            &copy; {{ date('Y') }} f33.click. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>