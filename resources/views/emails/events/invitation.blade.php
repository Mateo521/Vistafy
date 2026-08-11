<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; color: #1e293b; padding: 20px; }
        .container { max-w-[600px] margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 3px; }
        .btn { display: inline-block; background-color: #000000; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hola, fotógrafo de f33.</h2>
        <p><strong>{{ $inviter->business_name }}</strong> te invitó a sumarte como fotógrafo colaborador en el evento <strong>"{{ $event->name }}"</strong>.</p>
        <p>Al aceptar la invitación, vas a poder subir tus propias fotos a la galería de este evento, asignar tus lugares de cobertura y vender tu material directamente a los asistentes.</p>
        
        <p>Ingresá a tu panel de control en F33 para aceptar o rechazar la solicitud:</p>
        
        <a href="{{ route('photographer.dashboard') }}" class="btn">Ir a mi panel.</a>
        
        <p style="margin-top: 30px; font-size: 12px; color: #64748b;">El equipo de f33.click</p>
    </div>
</body>
</html>