@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-yellow-50 to-orange-100 flex items-center justify-center p-6">
    <div class="max-w-md w-full">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header -->
            <div class="bg-gradient-to-r from-yellow-500 to-orange-500 p-8 text-white text-center">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h1 class="text-3xl font-bold mb-2">Pago Pendiente</h1>
                <p class="text-yellow-100">Tu pago está siendo procesado</p>
            </div>

            <!-- Contenido -->
            <div class="p-8">
                <div class="text-center mb-6">
                    <p class="text-gray-600 mb-4">
                        Tu compra está en proceso de confirmación. Recibirás un email cuando esté lista para descargar.
                    </p>
                    
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-left">
                        <p class="text-sm text-yellow-800">
                            <strong>Estado:</strong> {{ ucfirst($purchase->status) }}
                        </p>
                        <p class="text-sm text-yellow-800 mt-2">
                            <strong>Compra ID:</strong> #{{ $purchase->id }}
                        </p>
                    </div>
                </div>

                <a href="{{ route('home') }}" 
                   class="block w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition text-center">
                    Volver al inicio
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
