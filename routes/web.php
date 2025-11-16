<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Rutas Públicas
|--------------------------------------------------------------------------
*/

// Página principal con video
Route::get('/', [PublicGalleryController::class, 'index'])->name('home');

// Galería pública
Route::prefix('galeria')->name('gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'gallery'])->name('index');
    Route::get('/{uniqueId}', [PublicGalleryController::class, 'show'])->name('show');
    Route::post('/buscar', [PublicGalleryController::class, 'search'])->name('search');
    Route::get('/foto/{uniqueId}/disponibilidad', [PublicGalleryController::class, 'checkAvailability'])->name('check');
});

// Eventos públicos
Route::prefix('eventos')->name('events.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'events'])->name('index');
    Route::get('/{slug}', [PublicGalleryController::class, 'showEvent'])->name('show');
});

// Fotógrafos públicos
Route::prefix('fotografos')->name('photographers.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'photographers'])->name('index');
    Route::get('/{id}', [PublicGalleryController::class, 'showPhotographer'])->name('show');
});

// Descargas (protegido por middleware de pago en el futuro)
Route::get('/descargar/{uniqueId}', [PublicGalleryController::class, 'download'])
    ->name('photo.download')
    ->middleware('auth'); // Por ahora requiere autenticación

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
            'total_events' => \App\Models\Event::whereHas('photos', function($q) use ($photographer) {
                $q->where('photographer_id', $photographer->id);
            })->count(),
        ];
        
        // Fotos recientes (últimas 12)
        $recentPhotos =$photographer->photos()
            ->latest()
            ->take(12)
            ->get();
        
        // Eventos recientes (últimos 6)
        $recentEvents = \App\Models\Event::whereHas('photos', function($q) use ($photographer) {
            $q->where('photographer_id', $photographer->id);
        })
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

    // Perfil del fotógrafo
    Route::get('/mi-perfil', [App\Http\Controllers\Photographer\ProfileController::class, 'show'])->name('profile');
    Route::get('/mi-perfil/editar', [App\Http\Controllers\Photographer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mi-perfil/actualizar', [App\Http\Controllers\Photographer\ProfileController::class, 'update'])->name('profile.update');

    // Gestión de fotos (se crearán a continuación)
    Route::resource('fotos', App\Http\Controllers\Photographer\PhotoController::class)->names('photos');
    
    // Gestión de eventos (se crearán a continuación)
    Route::resource('eventos', App\Http\Controllers\Photographer\EventController::class)->names('events');
});

require __DIR__.'/auth.php';
