<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\Photographer\EventController;
use App\Http\Controllers\Photographer\PhotoController;
use App\Http\Controllers\Photographer\ProfileController as PhotographerProfileController;
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

Route::prefix('fotografos')->name('photographers.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'photographers'])->name('index');
    Route::get('/{id}', [PublicGalleryController::class, 'showPhotographer'])->name('show');
});

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
| Rutas del Panel de Fotógrafo
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'photographer'])->prefix('fotografo')->name('photographer.')->group(function () {
    
    // Dashboard
    Route::get('/panel', function () {
        $photographer = auth()->user()->photographer;

        $stats = [
            'total_photos' => $photographer->photos()->count(),
            'active_photos' => $photographer->photos()->where('is_active', true)->count(),
            'total_downloads' => $photographer->photos()->sum('downloads'),
            'total_events' => \App\Models\Event::where('photographer_id', $photographer->id)->count(),
        ];

        $recentPhotos =$photographer->photos()->latest()->take(12)->get();
        
        $recentEvents = \App\Models\Event::where('photographer_id', $photographer->id)
            ->withCount('photos')
            ->latest()
            ->take(6)
            ->get();

        return Inertia::render('Photographer/Dashboard', [
            'photographer' => $photographer,
            'stats' => $stats,
            'recentPhotos' => $recentPhotos,
            'recentEvents' => $recentEvents,
        ]);
    })->name('dashboard');

    // Perfil
    Route::get('/mi-perfil', [PhotographerProfileController::class, 'show'])->name('profile');
    Route::get('/mi-perfil/editar', [PhotographerProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mi-perfil/actualizar', [PhotographerProfileController::class, 'update'])->name('profile.update');

    // Fotos
    Route::resource('fotos', PhotoController::class)->names('photos');

    // Eventos - RUTAS EXPLÍCITAS
    Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
    Route::get('/eventos/crear', [EventController::class, 'create'])->name('events.create');
    Route::post('/eventos', [EventController::class, 'store'])->name('events.store');
    Route::get('/eventos/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/eventos/{event}/editar', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/eventos/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/eventos/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/eventos/{event}/cover-image', [EventController::class, 'updateCoverImage'])->name('events.cover-image');
});

require __DIR__ . '/auth.php';
