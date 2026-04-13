<!DOCTYPE html>
<html>

<head>
    <style>
        body {
            font-family: sans-serif;
            line-height: 1.6;
            color: #333;
        }

        .button {
            background-color: #000;
            color: #fff;
            padding: 12px 24px;
            text-decoration: none;
            font-weight: bold;
            border-radius: 4px;
            display: inline-block;
        }

        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #eee;
        }

        .footer {
            font-size: 12px;
            color: #999;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2>¡Hola! Gracias por elegir f33</h2>
        <p>Tu pago ha sido procesado con éxito. Ya podés acceder a tus fotografías originales en alta calidad.</p>

        @if($temporaryPassword)
            <div style="background: #f9f9f9; padding: 15px; border-radius: 4px; margin: 20px 0;">
                <p style="margin: 0;"><strong>Se ha creado una cuenta para vos:</strong></p>
                <p style="margin: 5px 0;">Usuario: {{ $purchase->buyer_email }}</p>
                <p>Tu contraseña temporal es: <strong>{{ $temporaryPassword }}</strong></p>
                 <small>(Te recomendamos cambiarla al ingresar)</small>
            </div>
        @endif

        <p>Hacé clic en el siguiente botón para descargar tus archivos:</p>

        <p style="text-align: center; margin: 30px 0;">
            <a href="{{ route('purchase.download', $purchase->order_token) }}" class="button">
                Descargar Fotografías
            </a>
        </p>

        <p>Si el botón no funciona, copiá y pegá este link en tu navegador:<br>
            {{ route('purchase.download', $purchase->order_token) }}</p>



        <div class="footer">
            <p>Este es un correo automático de f33 (f33.click).<br>
                Si tenés algún problema con tu descarga, respondé a este mail.</p>
        </div>
    </div>
</body>

</html>