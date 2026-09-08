<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compra completada</title>
</head>

<body style="margin: 0; padding: 0; background-color: #F8F9FA; -webkit-font-smoothing: antialiased;">
    
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #F8F9FA; width: 100%; border-collapse: collapse;">
        <tr>
            <td align="center" style="padding: 40px 20px;">
                
                
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 4px; border-collapse: separate; overflow: hidden; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">

                
                    <tr>
                        <td style="padding: 40px 30px; text-align: center; border-bottom: 1px solid #f1f5f9; background-color: #ffffff;">
                            <div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #E30613; font-size: 14px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 12px;">
                                F33
                            </div>
                            <h1 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #0f172a; margin: 0 0 15px 0; font-size: 32px; letter-spacing: -0.5px; font-weight: 800;">
                                Compra completada
                            </h1>
                            <div style="display: inline-block; background-color: #fef2f2; border: 1px solid #fee2e2; color: #E30613; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; font-weight: bold; padding: 6px 16px; border-radius: 50px;">
                                Orden #{{ $purchase->id }}
                            </div>
                        </td>
                    </tr>

                    
                    <tr>
                        <td style="padding: 40px 30px;">
                            
                        
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #334155; font-size: 16px; line-height: 1.6; margin: 0 0 16px 0;">
                                Hola, <strong style="color: #0f172a;">{{ $purchase->buyer_name ?: 'Cliente' }}</strong>.
                            </p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #64748b; font-size: 15px; line-height: 1.6; margin: 0 0 32px 0;">
                                Tu pago fue procesado exitosamente. Las fotografías ya están listas para ser descargadas.
                            </p>

                           
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: 4px; margin: 0 0 40px 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px;">
                                        <h3 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0 0 20px 0; color: #94a3b8; font-size: 12px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid #e2e8f0; padding-bottom: 12px;">
                                            Resumen de la transacción
                                        </h3>

                                        <table width="100%" cellpadding="0" cellspacing="0" style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 14px;">
                                            <tr>
                                                <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Fecha:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #0f172a; font-weight: bold;">
                                                    {{ $purchase->created_at->format('d/m/Y H:i') }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #64748b; font-weight: 500;">Archivos Adquiridos:</td>
                                                <td style="padding: 8px 0; text-align: right; color: #0f172a; font-weight: bold;">
                                                    {{ $purchase->items->count() }} Fotografías
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 16px 0 0 0; color: #0f172a; font-weight: bold; border-top: 1px solid #e2e8f0; margin-top: 8px;">Total Abonado:</td>
                                                <td style="padding: 16px 0 0 0; text-align: right; border-top: 1px solid #e2e8f0; margin-top: 8px;">
                                                    <strong style="font-size: 20px; color: #E30613;">
                                                        ${{ number_format($purchase->total_amount, 2) }} {{ $purchase->currency }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            
                            <h3 style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; margin: 0 0 20px 0; color: #0f172a; font-size: 18px; font-weight: bold;">
                                Tus fotografías
                            </h3>

                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 40px 0; border-collapse: collapse; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;">
                                @foreach($purchase->items as $item)
                                    <tr>
                                        <td style="padding: 20px 0; border-bottom: 1px solid #f1f5f9;">
                                            <table width="100%" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    
                                                    <td width="90" valign="middle" style="padding-right: 20px;">
                                                        <img src="{{ $item->photo->thumbnail_url }}" alt="Asset F33" width="90" style="display: block; background-color: #f1f5f9; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0;" />
                                                    </td>
                                                    
                                                    
                                                    <td valign="middle">
                                                        <div style="color: #0f172a; font-weight: bold; font-size: 15px; margin-bottom: 6px;">
                                                            {{ $item->photo->title ?: "REF-{$item->photo->unique_id}" }}
                                                        </div>
                                                        @if($item->photo->event)
                                                            <div style="color: #64748b; font-size: 12px; margin-bottom: 16px;">
                                                                Evt: {{ $item->photo->event->name }}
                                                            </div>
                                                        @endif
                                                        
                                                        <a href="{{ route('purchases.download', ['purchase' => $purchase->id, 'photo' => $item->photo_id]) }}" style="display: inline-block; background-color: #f8fafc; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; padding: 8px 16px; border-radius: 50px; font-weight: bold; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">
                                                            ↓ Descargar
                                                        </a>
                                                    </td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>

                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin: 40px 0;">
                                <tr>
                                    <td align="center">
                                        <a href="{{ route('purchases.index') }}" style="display: inline-block; background-color: #000000; color: #ffffff; text-decoration: none; padding: 18px 36px; border-radius: 50px; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-weight: bold; font-size: 14px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                                            Ir a mi biblioteca
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            
                            <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border-radius: 4px; margin: 30px 0 0 0; border-collapse: collapse;">
                                <tr>
                                    <td style="padding: 24px; text-align: center;">
                                        <p style="margin: 0; color: #64748b; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 13px; line-height: 1.6;">
                                            <strong style="color: #334155;">¿Necesitás ayuda técnica?</strong><br>
                                            Podés descargar tus fotos desde tu historial en la web. Si tenés inconvenientes, escribinos a: <br>
                                            <a href="mailto:contacto@f33.click" style="color: #E30613; text-decoration: none; font-weight: bold;">contacto@f33.click</a>
                                        </p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                
                
                <table width="600" cellpadding="0" cellspacing="0" style="margin: 0 auto;">
                    <tr>
                        <td style="padding: 30px 20px; text-align: center;">
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #94a3b8; font-size: 11px; margin: 0 0 10px 0; line-height: 1.5; font-weight: bold;">
                                CORREO GENERADO AUTOMÁTICAMENTE. POR FAVOR NO RESPONDAS A ESTA DIRECCIÓN.
                            </p>
                            <p style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #cbd5e1; font-size: 11px; margin: 0;">
                                © {{ date('Y') }} F33. Todos los derechos reservados.
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>

</html>