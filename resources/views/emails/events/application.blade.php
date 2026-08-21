<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; color: #1e293b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 4px;   }
        .btn { display: inline-block; background-color: #000000; color: #ffffff; padding: 12px 24px; text-decoration: none; border-radius: 4px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Hola, {{ $event->photographer->user->name }}.</h2>
        <p>El fotógrafo <strong>{{ $applicant->business_name }}</strong> solicitó unirse a la cobertura de tu evento <strong>"{{ $event->title }}"</strong>.</p>

        <p>Ingresá a tu panel de control en F33 para revisar su perfil, aceptar su solicitud o rechazarla:</p>
        
        <a href="{{ route('photographer.dashboard') }}" class="btn">Ir a mi panel de control</a>
    </div>
</body>
</html>