<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra completada | F33</title>
</head>

<body style="margin: 0; padding: 0; background-color: #050505; -webkit-font-smoothing: antialiased;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #050505; width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                <!-- Contenedor Principal -->
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #000000; border: 2px solid #3f3f46; border-collapse: collapse; margin: 0 auto;">

                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; border-bottom: 2px solid #E30613; background-color: #09090b;">
                            <div style="font-family: 'Courier New', Courier, monospace; color: #E30613; font-size: 11px; font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px;">
                                >_ CONFIRMACIÓN DE COMPRA
                            </div>
                            <h1 style="font-family: Impact, 'Arial Black', Arial, sans-serif; color: #ffffff; margin: 0 0 15px 0; font-size: 36px; text-transform: uppercase; letter-spacing: 1px; font-weight: normal;">
                                Transacción_OK
                            </h1>
                            <div style="display: inline-block; background-color: #E30613; color: #000000; font-family: 'Courier New', Courier, monospace; font-size: 12px; font-weight: bold; padding: 4px 10px; letter-spacing: 1px;">
                                ORDEN_ID: #{{ $purchase->id }}
                            </div>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px 30px;">
                            <p style="font-family: 'Courier New', Courier, monospace; color: #ffffff; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0; text-transform: uppercase;">
                                HOLA, <strong style="color: #E30613;">{{ $purchase->buyer_name ?: 'CLIENTE' }}</strong>.
                            </p>

                            <p style="font-family: 'Courier New', Courier, monospace; color: #a1a1aa; font-size: 13px; line-height: 1.6; margin: 0 0 30px 0;">
                                Pago exitoso. Las fotografías ya se encuentran para ser descargados en alta resolución.
                            </p>

                            <!-- Order Summary -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #09090b; border: 1px solid #27272a; border-left: 4px solid #E30613; margin: 0 0 40px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="font-family: 'Courier New', Courier, monospace; margin: 0 0 20px 0; color: #ffffff; font-size: 12px; text-transform: uppercase; letter-spacing: 2px; border-bottom: 1px dashed #3f3f46; padding-bottom: 10px;">
                                            Resumen
                                        </h3>

                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Courier New', Courier, monospace; color: #a1a1aa; font-size: 13px;">
                                            <tr>
                                                <td style="padding: 8px 0; text-transform: uppercase;">> Fecha:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #ffffff;">
                                                    {{ $purchase->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; text-transform: uppercase;">> Fotografías:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #ffffff;">
                                                    {{ $purchase->items->count() }} ACTIVOS
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 15px 0 5px 0; text-transform: uppercase; font-weight: bold; color: #ffffff; border-top: 1px dashed #3f3f46; margin-top: 10px;">> Total_Abonado:</td>
                                                <td style="padding: 15px 0 5px 0; text-align: right; border-top: 1px dashed #3f3f46; margin-top: 10px;">
                                                    <strong style="font-size: 18px; color: #E30613;">
                                                        ${{ number_format($purchase->total_amount, 2) }} {{ $purchase->currency }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Photos List -->
                            <h3 style="font-family: 'Courier New', Courier, monospace; margin: 0 0 15px 0; color: #ffffff; font-size: 12px; text-transform: uppercase; letter-spacing: 2px;">
                                [ Detalle ]
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 40px 0; border-collapse: collapse; font-family: 'Courier New', Courier, monospace;">
                                @foreach($purchase->items as $item)
                                    <tr>
                                        <td style="padding: 15px 0; border-bottom: 1px solid #27272a;">
                                            <div style="color: #ffffff; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-bottom: 4px;">
                                                {{ $item->photo->title ?: "ASSET_#{$item->photo->unique_id}" }}
                                            </div>
                                            @if($item->photo->event)
                                                <div style="color: #71717a; font-size: 11px; text-transform: uppercase;">
                                                    > EVT: {{ $item->photo->event->name }}
                                                </div>
                                            @endif
                                        </td>
                                        <td style="padding: 15px 0; text-align: right; color: #a1a1aa; font-size: 13px; border-bottom: 1px solid #27272a;">
                                            ${{ number_format($item->unit_price, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            <!-- CTA Button -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('purchases.index') }}" style="display: inline-block; background-color: #E30613; color: #000000; text-decoration: none; padding: 18px 40px; font-family: 'Courier New', Courier, monospace; font-weight: bold; font-size: 14px; text-transform: uppercase; letter-spacing: 2px; border: 1px solid #E30613;">
                                            Fotografías
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Help Section -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #09090b; border: 1px solid #27272a; margin: 30px 0 0 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 20px; text-align: center;">
                                        <p style="margin: 0; color: #a1a1aa; font-family: 'Courier New', Courier, monospace; font-size: 11px; line-height: 1.6; text-transform: uppercase;">
                                            <strong style="color: #ffffff;">¿Fallo en el sistema?</strong><br>
                                            Podés descargar tus fotos de manera permanente desde tu biblioteca digital. Para soporte técnico, contactar a: <br>
                                            <a href="mailto:contacto@f33.click" style="color: #E30613; text-decoration: none; font-weight: bold;">contacto@f33.click</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #050505; padding: 30px; text-align: center; border-top: 1px dashed #3f3f46;">
                            <p style="font-family: 'Courier New', Courier, monospace; color: #52525b; font-size: 10px; margin: 0 0 10px 0; line-height: 1.5; text-transform: uppercase;">
                            GENERADO AUTOMÁTICAMENTE. NO RESPONDER A ESTE CORREO.
                            </p>
                            <p style="font-family: 'Courier New', Courier, monospace; color: #3f3f46; font-size: 10px; margin: 0; letter-spacing: 1px;">
                                © {{ date('Y') }} F33. TODOS LOS DERECHOS RESERVADOS.
                            </p>
                        </td>
                    </tr>
                </table>
                
                <!-- Spacer for bottom margin -->
                <div style="height: 40px;"></div>
            </td>
        </tr>
    </table>
</body>

</html>