@extends('layouts.app')

@section('content')
    <div class="container mx-auto p-6">
        <div class="max-w-2xl mx-auto">
            <h1 class="text-3xl font-bold mb-6">Comprar Foto #{{ $photo->id }}</h1>

            <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
                <img src="{{ Storage::url($photo->path) }}" alt="Foto" class="w-full rounded mb-4">
                <p class="text-2xl font-bold">${{ number_format($photo->price, 2) }} ARS</p>
            </div>

            <!-- Brick de Payment -->
            <div id="paymentBrick_container"></div>
        </div>
    </div>

    <script>
        // Inicializar Mercado Pago
        const mp = new MercadoPago('{{ config('services.mercadopago.public_key') }}', {
            locale: 'es-AR'
        });

        // Configurar Payment Brick
        const bricksBuilder = mp.bricks();

        bricksBuilder.create('payment', 'paymentBrick_container', {
            initialization: {
                amount: {{ $photo->price }},
                payer: {
                    email: '{{ $buyerEmail }}',
                },
            },
            customization: {
                paymentMethods: {
                    creditCard: 'all',
                    debitCard: 'all',
                    maxInstallments: 1,
                },
                visual: {
                    style: {
                        theme: 'default',
                    },
                },
            },
            callbacks: {
                onReady: () => {
                },
                onSubmit: async (formData) => {
                    try {
                        // Enviar al backend
                        const response = await fetch('{{ route('payment.process', ['photo' => $photo->id]) }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify(formData),
                        });

                        const result = await response.json();

                        if (result.status === 'approved') {
                            window.location.href = result.redirect_url;
                        } else {
                            alert('Error en el pago: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Error procesando el pago');
                    }
                },
                onError: (error) => {
                    console.error('Error en Payment Brick:', error);
                },
            },
        });
    </script>
@endsection