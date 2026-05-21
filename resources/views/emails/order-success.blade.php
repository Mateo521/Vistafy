<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago confirmado - f33</title>
</head>

<body style="font-family: 'Courier New', Courier, monospace; margin: 0; padding: 0; background-color: #000000;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #000000; padding: 40px 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #18181b; border: 1px solid #3f3f46; border-radius: 0px; overflow: hidden;">

                    <tr>
                        <td
                            style="background-color: #09090b; padding: 40px; text-align: center; border-bottom: 2px solid #dc2626;">
                            <h1
                                style="color: #ffffff; margin: 0 0 10px 0; font-size: 28px; font-weight: 900; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: -1px;">
                                Transacción <span style="color: #dc2626;">Exitosa</span>
                            </h1>
                            <p
                                style="color: #71717a; margin: 0; font-size: 12px; letter-spacing: 0.2em; text-transform: uppercase;">
                                ID de operación #{{ $purchase->id }}
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 40px;">
                            <p
                                style="color: #e4e4e7; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0; text-transform: uppercase;">
                                SALUDOS <strong>{{ $purchase->buyer_name ?: 'USUARIO' }}</strong>,
                            </p>
                            <p style="color: #a1a1aa; font-size: 14px; line-height: 1.6; margin: 0 0 30px 0;">
                                . Tu material en alta resolución ya está disponible para descarga.
                            </p>

                            @if($temporaryPassword)
                                <div
                                    style="background-color: #000000; border-left: 4px solid #dc2626; padding: 20px; margin: 0 0 30px 0;">
                                    <h3
                                        style="margin: 0 0 10px 0; color: #dc2626; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em;">
                                        [ Credenciales de acceso ]
                                    </h3>
                                    <p style="color: #a1a1aa; font-size: 13px; margin: 0 0 5px 0;">Usuario:
                                        <strong>{{ $purchase->buyer_email }}</strong></p>
                                    <p style="color: #a1a1aa; font-size: 13px; margin: 0 0 10px 0;">Clave Temporal: <strong
                                            style="color: #ffffff;">{{ $temporaryPassword }}</strong></p>
                                    <p style="color: #52525b; font-size: 11px; margin: 0;">(Te recomendamos cambiar esta
                                        clave al ingresar a la plataforma).</p>
                                </div>
                            @endif

                            <div
                                style="background-color: #09090b; border: 1px solid #27272a; padding: 20px; margin: 0 0 30px 0;">
                                <h3
                                    style="margin: 0 0 15px 0; color: #ffffff; font-size: 12px; text-transform: uppercase; letter-spacing: 0.1em; font-family: Arial, sans-serif;">
                                    Resumen del objetivo
                                </h3>
                                <table width="100%" cellpadding="5" cellspacing="0"
                                    style="color: #a1a1aa; font-size: 13px;">
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #27272a;">
                                            <strong>Fecha:</strong></td>
                                        <td
                                            style="padding: 8px 0; text-align: right; border-bottom: 1px solid #27272a;">
                                            {{ $purchase->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0;"><strong>Inversión Total:</strong></td>
                                        <td style="padding: 12px 0; text-align: right;"><strong
                                                style="font-size: 18px; color: #ffffff;">${{ number_format($purchase->total_amount, 2) }}
                                                {{ $purchase->currency }}</strong></td>
                                    </tr>
                                </table>
                            </div>

                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ route('payment.download', $purchase->order_token) }}"
                                    style="display: inline-block; background-color: #ffffff; color: #000000; text-decoration: none; padding: 16px 40px; font-weight: bold; font-size: 12px; font-family: Arial, sans-serif; text-transform: uppercase; letter-spacing: 0.1em;">
                                    [ DESCARGAR ARCHIVOS ORIGINALES ]
                                </a>
                            </div>

                            <p style="text-align: center; color: #52525b; font-size: 10px; margin-top: 20px;">
                                Si el botón falla, accedé manualmente:<br>
                                <a href="{{ route('payment.download', $purchase->order_token) }}"
                                    style="color: #dc2626;">{{ route('payment.download', $purchase->order_token) }}</a>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td
                            style="background-color: #09090b; padding: 30px; text-align: center; border-top: 1px solid #27272a;">
                            <p
                                style="color: #52525b; font-size: 11px; margin: 0 0 10px 0; line-height: 1.5; text-transform: uppercase;">
                                automático de f33.click. Si necesitás asistencia, contactanos.
                            </p>
                            <p style="color: #3f3f46; font-size: 10px; margin: 0;">
                                © {{ date('Y') }} f33 PHOTOGRAPHY. DERECHOS RESERVADOS.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>