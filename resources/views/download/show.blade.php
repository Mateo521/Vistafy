@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center p-6">
        <div class="max-w-2xl w-full">
            <!-- Card principal -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Header -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 p-8 text-white text-center">
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold mb-2">¡Pago Exitoso!</h1>
                    <p class="text-green-100">Tu foto está lista para descargar</p>
                </div>

                <!-- Contenido -->
                <div class="p-8">
                    <!-- Preview de la foto -->
                    @if($photo)
                        <div class="mb-6">
                            <div class="relative rounded-lg overflow-hidden shadow-lg">
                                <img src="{{ Storage::url($photo->path) }}" alt="Foto #{{ $photo->id }}" class="w-full h-auto">
                                <div
                                    class="absolute top-4 right-4 bg-black/50 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                                    Foto #{{ $photo->id }}
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Información de la compra -->
                    <div class="bg-gray-50 rounded-lg p-6 mb-6">
                        <h3 class="font-semibold text-gray-800 mb-4">Detalles de la compra</h3>
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Compra ID:</span>
                                <span class="font-mono text-gray-800">#{{ $purchase->id }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Email:</span>
                                <span class="text-gray-800">{{ $purchase->buyer_email }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Monto:</span>
                                <span class="font-semibold text-gray-800">${{ number_format($purchase->amount, 2) }}
                                    ARS</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Fecha:</span>
                                <span class="text-gray-800">{{ $purchase->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($purchase->download_count > 0)
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Descargas:</span>
                                    <span class="text-gray-800">{{ $purchase->download_count }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Botón de descarga -->
                    <a href="{{ route('download.file', ['token' => $purchase->download_token]) }}"
                        class="block w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 px-6 rounded-lg transition duration-200 text-center shadow-lg hover:shadow-xl">
                        <div class="flex items-center justify-center space-x-3">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            <span>Descargar Foto en Alta Resolución</span>
                        </div>
                    </a>

                    <!-- Nota informativa -->
                    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-start space-x-3">
                            <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                    clip-rule="evenodd"></path>
                            </svg>
                            <div class="text-sm text-blue-800">
                                <p class="font-semibold mb-1">Importante:</p>
                                <ul class="list-disc list-inside space-y-1 text-blue-700">
                                    <li>Guarda este enlace para descargar la foto en cualquier momento</li>
                                    <li>La foto se descargará en su máxima calidad</li>
                                    <li>El enlace no expira</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Volver al inicio -->
                    <div class="mt-6 text-center">
                        <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                            ← Volver al inicio
                        </a>
                    </div>
                </div>
            </div>

            <!-- Soporte -->
            <div class="mt-6 text-center text-sm text-gray-600">
                <p>¿Problemas con la descarga? <a href="mailto:soporte@vistafy.com"
                        class="text-blue-600 hover:underline">Contáctanos</a></p>
            </div>
        </div>
    </div>
@endsection