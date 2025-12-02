<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\Photographer\EventController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\Photographer\PhotoController;
use App\Http\Controllers\Photographer\ProfileController as PhotographerProfileController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\Auth\PhotographerRegistrationController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

Route::get('/', [PublicGalleryController::class, 'index'])->name('home');

Route::prefix('galeria')->name('gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'gallery'])->name('index');
    Route::get('/{uniqueId}', [PublicGalleryController::class, 'show'])->name('show');
    Route::post('/buscar', [PublicGalleryController::class, 'search'])->name('search');
    Route::get('/foto/{uniqueId}/disponibilidad', [PublicGalleryController::class, 'checkAvailability'])->name('check');
});

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

    // Callbacks de Mercado Pago (español)
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

// Descarga directa
Route::get('/pago/descargar/{token}', [DownloadController::class, 'download'])
    ->name('download.file');

// Página de descarga (opcional, con botón)
Route::get('/downloads/{token}', [DownloadController::class, 'show'])
    ->name('download.show');

Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);

/*
|--------------------------------------------------------------------------
| Rutas de Descarga (Autenticadas)
|--------------------------------------------------------------------------
*/

Route::get('/descargar/{uniqueId}', [PublicGalleryController::class, 'download'])
    ->name('photo.download')
    ->middleware('auth');

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
        Route::get('/', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'index'])->name('index');
        Route::get('/{photographer}', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'show'])->name('show'); //  NUEVA - Ver detalles
        Route::post('/{photographer}/aprobar', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'approve'])->name('approve');
        Route::post('/{photographer}/rechazar', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'reject'])->name('reject');
        Route::post('/{photographer}/revertir', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'revert'])->name('revert'); //  NUEVA - Revertir rechazo
        Route::post('/{photographer}/suspender', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'suspend'])->name('suspend');
        Route::post('/{photographer}/reactivar', [\App\Http\Controllers\Admin\PhotographerManagementController::class, 'reactivate'])->name('reactivate');
    });
});



require __DIR__ . '/auth.php';
