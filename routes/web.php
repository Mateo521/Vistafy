<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\Photographer\EventController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\Photographer\PhotoController;
use App\Http\Controllers\Photographer\ProfileController as PhotographerProfileController;
use App\Http\Controllers\Admin\PhotographerManagementController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\PaymentSimulationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\PhotographerRegistrationController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\Photographer\FutureEventManagementController;

use App\Http\Controllers\FutureEventController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicGalleryController::class, 'index'])->name('home');

//  EVENTOS FUTUROS - SOLO EN ESPAÑOL
Route::prefix('eventos-futuros')->name('future-events.')->group(function () {
    // API para el mapa (JSON)
    Route::get('/api', [FutureEventController::class, 'index'])->name('api');

    // Detalle de evento futuro
    Route::get('/{futureEvent}', [FutureEventController::class, 'show'])->name('show');
});

Route::prefix('galeria')->name('gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'gallery'])->name('index');
    Route::get('/{uniqueId}', [PublicGalleryController::class, 'show'])->name('show');
    Route::post('/buscar', [PublicGalleryController::class, 'search'])->name('search');
    Route::get('/foto/{uniqueId}/disponibilidad', [PublicGalleryController::class, 'checkAvailability'])->name('check');
});

//  EVENTOS (pasados/realizados) - EN ESPAÑOL
Route::prefix('eventos')->name('events.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'events'])->name('index');
    Route::get('/{slug}', [PublicGalleryController::class, 'showEvent'])->name('show');
});

Route::get('/fotografos', [PhotographerController::class, 'index'])->name('photographers.index');
Route::get('/fotografos/{slug}', [PhotographerController::class, 'show'])->name('photographers.show');


/*
|--------------------------------------------------------------------------
|  NUEVO: Registro de Fotógrafos (Público)
|--------------------------------------------------------------------------
*/

Route::get('/registro-fotografo', [PhotographerRegistrationController::class, 'create'])
    ->name('photographer.register');
Route::post('/registro-fotografo', [PhotographerRegistrationController::class, 'store']);

/*
|--------------------------------------------------------------------------
| Rutas de Pago - Mercado Pago
|--------------------------------------------------------------------------
*/

Route::prefix('pago')->name('payment.')->group(function () {
    // Iniciar compra
    Route::post('/fotos/{photo}/comprar', [PaymentController::class, 'initiatePurchase'])
        ->name('initiate');


    Route::post('/carrito/comprar', [PaymentController::class, 'initiateCartPurchase'])
        ->middleware('auth')
        ->name('initiate.cart');

    //  SIMULADOR (solo en local)
    if (app()->environment('local') && config('services.mercadopago.simulation_mode')) {
        Route::get('/simular/{purchase}', [PaymentSimulationController::class, 'show'])
            ->name('simulate');

        Route::post('/simular/{purchase}', [PaymentSimulationController::class, 'process'])
            ->name('simulate.process');
    }


    // Callbacks de Mercado Pago (español)
    //  IMPORTANTE: Ahora reciben 'purchase_id' como parámetro de query
    Route::get('/exito', [PaymentController::class, 'success'])->name('success');
    Route::get('/fallo', [PaymentController::class, 'failure'])->name('failure');
    Route::get('/pendiente', [PaymentController::class, 'pending'])->name('pending');

    // Descarga con token
    Route::get('/descargar/{token}', [PaymentController::class, 'download'])->name('download');
});


Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success.en');
Route::get('/payment/failure', [PaymentController::class, 'failure'])->name('payment.failure.en');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending.en');

Route::get('/purchases/{purchase}/check-status', [PurchaseController::class, 'checkStatus'])
    ->name('purchases.check-status');




// Página de descarga (opcional, con botón)
Route::get('/descargar/{token}', [DownloadController::class, 'download'])
    ->name('download');  // ← Nombre: payment.download

Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);

/*
|--------------------------------------------------------------------------
| Rutas de Descarga (Autenticadas)
|--------------------------------------------------------------------------
*/

Route::get('/descargar/{uniqueId}', [PublicGalleryController::class, 'download'])
    ->name('photo.download')
    ->middleware('auth');



//  Compras del Usuario
Route::middleware('auth')->prefix('mis-compras')->name('purchases.')->group(function () {
    Route::get('/', [PurchaseHistoryController::class, 'index'])->name('index');
    Route::get('/{purchase}/descargar/{photo}', [PurchaseHistoryController::class, 'download'])->name('download');
    Route::get('/{purchase}/descargar-todas', [PurchaseHistoryController::class, 'downloadAll'])->name('download.all');
});


/*
|--------------------------------------------------------------------------
| Rutas de Autenticación
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});








Route::middleware('auth')->prefix('carrito')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/agregar/{photo}', [CartController::class, 'add'])->name('add');
    Route::delete('/eliminar/{photoId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/vaciar', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});




/*
|--------------------------------------------------------------------------
|  NUEVO: Páginas de Estado de Fotógrafo (Autenticadas)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth'])->prefix('fotografo')->name('photographer.')->group(function () {
    // Página de pendiente de aprobación
    Route::get('/pendiente', function () {
        return Inertia::render('Photographer/Pending');
    })->name('pending');

    // Página de cuenta rechazada
    Route::get('/rechazado', function () {
        $photographer = auth()->user()->photographer;
        return Inertia::render('Photographer/Rejected', [
            'reason' => $photographer?->rejection_reason,
        ]);
    })->name('rejected');

    // Página de cuenta suspendida
    Route::get('/suspendido', function () {
        return Inertia::render('Photographer/Suspended');
    })->name('suspended');
});

/*
|--------------------------------------------------------------------------
|  ACTUALIZADO: Panel de Fotógrafo (Requiere Aprobación)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'photographer.approved'])->prefix('fotografo')->name('photographer.')->group(function () {


    // --- PERFIL PROFESIONAL ---
    // El prefijo 'perfil' se suma a 'fotografo' -> /fotografo/perfil
    Route::prefix('perfil')->name('profile.')->group(function () {

        // 1. Mostrar el formulario
        // URL: /fotografo/perfil/editar
        // Nombre de ruta: photographer.profile.edit
        Route::get('/editar', [PhotographerProfileController::class, 'edit'])->name('edit');

        // 2. Guardar los cambios
        // URL: /fotografo/perfil/actualizar
        // Nombre de ruta: photographer.profile.update
        // Usamos PATCH porque es una actualización parcial
        Route::patch('/actualizar', [PhotographerProfileController::class, 'update'])->name('update');

        // 3. Eliminar fotos específicas
        Route::delete('/foto-perfil', [PhotographerProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::delete('/banner', [PhotographerProfileController::class, 'deleteBannerPhoto'])->name('banner.delete');
    });






    Route::prefix('oportunidades')->name('opportunities.')->group(function () {
        Route::get('/', [FutureEventManagementController::class, 'index'])->name('index');
        Route::get('/crear', [FutureEventManagementController::class, 'create'])->name('create');
        Route::post('/', [FutureEventManagementController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [FutureEventManagementController::class, 'edit'])->name('edit');
        Route::post('/{id}', [FutureEventManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [FutureEventManagementController::class, 'destroy'])->name('destroy');
    });






    // Dashboard
    Route::get('/panel', function () {
        $photographer = auth()->user()->photographer;

        $stats = [
            'total_events' => \App\Models\Event::where('photographer_id', $photographer->id)->count(),
            'total_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->count(),
            'active_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->where('is_active', true)->count(),
            'total_downloads' => \App\Models\Photo::where('photographer_id', $photographer->id)->sum('downloads'),
        ];

        $recentEvents = \App\Models\Event::where('photographer_id', $photographer->id)
            ->withCount('photos')
            ->latest()
            ->take(6)
            ->get();

        $recentPhotos = \App\Models\Photo::where('photographer_id', $photographer->id)
            ->with('event:id,name')
            ->latest()
            ->take(8)
            ->get();

        return Inertia::render('Photographer/Dashboard', [
            'stats' => $stats,
            'photographer' => $photographer,
            'recentEvents' => $recentEvents,
            'recentPhotos' => $recentPhotos,
        ]);
    })->name('dashboard');

    // Perfil
    Route::get('/perfil/editar', [App\Http\Controllers\Photographer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil/actualizar', [App\Http\Controllers\Photographer\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil/foto-perfil', [App\Http\Controllers\Photographer\ProfileController::class, 'deleteProfilePhoto'])->name('profile.photo.delete');
    Route::delete('/perfil/banner', [App\Http\Controllers\Photographer\ProfileController::class, 'deleteBannerPhoto'])->name('profile.banner.delete');

    // Fotos
    Route::get('/fotos', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/fotos/crear', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('/fotos', [PhotoController::class, 'store'])->name('photos.store');
    Route::get('/fotos/{photo}', [PhotoController::class, 'show'])->name('photos.show');
    Route::get('/fotos/{photo}/editar', [PhotoController::class, 'edit'])->name('photos.edit');
    Route::put('/fotos/{photo}', [PhotoController::class, 'update'])->name('photos.update');
    Route::delete('/fotos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    // Eventos
    Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
    Route::get('/eventos/crear', [EventController::class, 'create'])->name('events.create');
    Route::post('/eventos', [EventController::class, 'store'])->name('events.store');
    Route::get('/eventos/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/eventos/{event}/editar', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/eventos/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/eventos/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/eventos/{event}/cover-image', [EventController::class, 'updateCoverImage'])->name('events.cover-image');
});



/*
|--------------------------------------------------------------------------
| Rutas de Administrador
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard de admin
    Route::get('/panel', function () {
        $stats = [
            'total_photographers' => \App\Models\Photographer::count(),
            'pending_photographers' => \App\Models\Photographer::where('status', 'pending')->count(),
            'approved_photographers' => \App\Models\Photographer::where('status', 'approved')->count(),
            'rejected_photographers' => \App\Models\Photographer::where('status', 'rejected')->count(),
            'suspended_photographers' => \App\Models\Photographer::where('status', 'suspended')->count(),
            'total_events' => \App\Models\Event::count(),
            'total_photos' => \App\Models\Photo::count(),
            'total_users' => \App\Models\User::count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    })->name('dashboard');

    //  Gestión de fotógrafos
    Route::prefix('fotografos')->name('photographers.')->group(function () {
        Route::get('/', [PhotographerManagementController::class, 'index'])->name('index');

        //  Usar solo {photographer} - el modelo decidirá usar ID en admin
        Route::get('/{photographer}', [PhotographerManagementController::class, 'show'])->name('show');
        Route::post('/{photographer}/aprobar', [PhotographerManagementController::class, 'approve'])->name('approve');
        Route::post('/{photographer}/rechazar', [PhotographerManagementController::class, 'reject'])->name('reject');
        Route::post('/{photographer}/suspender', [PhotographerManagementController::class, 'suspend'])->name('suspend');
        Route::post('/{photographer}/reactivar', [PhotographerManagementController::class, 'reactivate'])->name('reactivate');
        Route::post('/{photographer}/revertir', [PhotographerManagementController::class, 'revert'])->name('revert');
    });

});



require __DIR__ . '/auth.php';
