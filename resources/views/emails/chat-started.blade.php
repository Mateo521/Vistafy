<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Coordinación de evento - F33</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f4f5; margin: 0; padding: 40px 20px; color: #333333;">

    <div style="max-w: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 3px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
        

        <div style="background-color: #000000; padding: 25px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px; letter-spacing: 1px;">
                F<span style="color: #E30613;">33</span>
            </h1>
        </div>


        <div style="padding: 30px;">
            <p style="font-size: 12px; font-weight: bold; color: #E30613; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 10px;">
                Actualización
            </p>
            
            <h2 style="font-size: 20px; margin-top: 0; margin-bottom: 20px; color: #111827;">
                El chat del evento comenzó
            </h2>

            <p style="font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 25px;">
                Hola, equipo. <strong>{{ $sender->business_name ?? $sender->user->name }}</strong> envió el primer mensaje en la sala de coordinación para el evento <strong>"{{ $event->name }}"</strong>.
            </p>

            <p style="font-size: 15px; line-height: 1.6; color: #4b5563; margin-bottom: 30px;">
                Ingresá ahora para coordinar los detalles de la cobertura, ubicaciones y logística antes de que comience el evento.
            </p>


            <div style="text-align: center;">
                <a href="{{ route('photographer.events.chat', $event->id) }}" 
                    style="display: inline-block; background-color: #E30613; color: #ffffff; text-decoration: none; padding: 14px 28px; border-radius: 50px; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 1px;">
                    Ir al chat
                </a>
            </div>
        </div>


        <div style="background-color: #f9fafb; padding: 20px; text-align: center; border-top: 1px solid #e5e7eb;">
            <p style="font-size: 12px; color: #9ca3af; margin: 0;">
                Este es un aviso automático de F33.<br>
                Por favor, no respondas directamente a este correo.
            </p>
        </div>

    </div>

</body>
</html>