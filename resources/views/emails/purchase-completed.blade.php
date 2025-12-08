<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra Completada</title>
</head>

<body
    style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; padding: 0; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <table width="600" cellpadding="0" cellspacing="0"
                    style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

                    <!-- Header -->
                    <tr>
                        <td
                            style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 40px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 28px; font-weight: bold;">
                                 ¡Compra Exitosa!
                            </h1>
                            <p
                                style="color: #94a3b8; margin: 0; font-size: 14px; letter-spacing: 0.05em; text-transform: uppercase;">
                                Orden #{{ $purchase->id }}
                            </p>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <p style="color: #334155; font-size: 16px; line-height: 1.6; margin: 0 0 20px 0;">
                                Hola <strong>{{ $purchase->buyer_name ?: 'Cliente' }}</strong>,
                            </p>

                            <p style="color: #334155; font-size: 16px; line-height: 1.6; margin: 0 0 30px 0;">
                                Tu pago ha sido procesado exitosamente. Ya puedes descargar tus fotografías en alta
                                resolución.
                            </p>

                            <!-- Order Summary -->
                            <div
                                style="background-color: #f1f5f9; border-left: 4px solid #0f172a; padding: 20px; margin: 0 0 30px 0; border-radius: 4px;">
                                <h3
                                    style="margin: 0 0 15px 0; color: #0f172a; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">
                                    Resumen de tu Compra
                                </h3>

                                <table width="100%" cellpadding="5" cellspacing="0"
                                    style="color: #475569; font-size: 14px;">
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                                            <strong>Fecha:</strong>
                                        </td>
                                        <td
                                            style="padding: 8px 0; text-align: right; border-bottom: 1px solid #e2e8f0;">
                                            {{ $purchase->created_at->format('d/m/Y H:i') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0;">
                                            <strong>Fotografías:</strong>
                                        </td>
                                        <td
                                            style="padding: 8px 0; text-align: right; border-bottom: 1px solid #e2e8f0;">
                                            {{ $purchase->items->count() }}
                                            {{ $purchase->items->count() === 1 ? 'foto' : 'fotos' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 12px 0;">
                                            <strong style="font-size: 16px;">Total:</strong>
                                        </td>
                                        <td style="padding: 12px 0; text-align: right;">
                                            <strong style="font-size: 20px; color: #0f172a;">
                                                ${{ number_format($purchase->total_amount, 2) }}
                                                {{ $purchase->currency }}
                                            </strong>
                                        </td>
                                    </tr>
                                </table>
                            </div>

                            <!-- Photos List -->
                            <h3
                                style="margin: 0 0 15px 0; color: #0f172a; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em;">
                                Fotografías Adquiridas
                            </h3>

                            <table width="100%" cellpadding="8" cellspacing="0" style="margin: 0 0 30px 0;">
                                @foreach($purchase->items as $item)
                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                        <td style="padding: 12px 0;">
                                            <div style="color: #0f172a; font-weight: 600; margin-bottom: 4px;">
                                                {{ $item->photo->title ?: "Foto #{$item->photo->unique_id}" }}
                                            </div>
                                            @if($item->photo->event)
                                                <div style="color: #64748b; font-size: 12px;">
                                                     {{ $item->photo->event->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="padding: 12px 0; text-align: right; color: #475569; font-weight: 600;">
                                            ${{ number_format($item->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <!-- CTA Button -->
                            <div style="text-align: center; margin: 40px 0;">
                                <a href="{{ route('purchases.index') }}"
                                    style="display: inline-block; background-color: #0f172a; color: #ffffff; text-decoration: none; padding: 16px 40px; border-radius: 6px; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                                    📥 Descargar mis Fotos
                                </a>
                            </div>

                            <!-- Help Section -->
                            <div
                                style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 30px 0 0 0; border-radius: 4px;">
                                <p style="margin: 0; color: #92400e; font-size: 13px; line-height: 1.5;">
                                    <strong>💡 ¿Necesitas ayuda?</strong><br>
                                    Puedes descargar tus fotos ilimitadamente desde tu cuenta. Si tienes alguna
                                    pregunta, contáctanos en
                                    <a href="mailto:soporte@empresa.com"
                                        style="color: #92400e; text-decoration: underline;">soporte@empresa.com</a>
                                </p>
                            </div>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td
                            style="background-color: #f8fafc; padding: 30px; text-align: center; border-top: 1px solid #e2e8f0;">
                            <p style="color: #94a3b8; font-size: 12px; margin: 0 0 10px 0; line-height: 1.5;">
                                Este email fue enviado porque completaste una compra en EMPRESA Photography.
                            </p>
                            <p style="color: #cbd5e1; font-size: 11px; margin: 0;">
                                © {{ date('Y') }} EMPRESA Photography. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>