<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; background-color: #f8f9fa; color: #1e293b; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; padding: 30px; border-radius: 4px; } 
    </style>
</head>
<body>
    <div class="container">
        <h2>Hola, {{ $applicant->user->name }}.</h2>
        <p>Te escribimos sobre tu solicitud para cubrir el evento <strong>"{{ $event->title }}"</strong>.</p>
        <p>En esta ocasión, el organizador ha decidido no avanzar con tu postulación. No te desanimes, hay muchas otras oportunidades y eventos buscando fotógrafos.</p>
        
        <p>Seguí explorando y postulándote a futuras misiones.</p>
    </div>
</body>
</html>