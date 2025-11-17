<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\Photographer\EventController;

use App\Http\Controllers\PhotographerController;
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
/*
Route::prefix('fotografos')->name('photographers.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'photographers'])->name('index');
    Route::get('/{id}', [PublicGalleryController::class, 'showPhotographer'])->name('show');
});
*/
Route::get('/descargar/{uniqueId}', [PublicGalleryController::class, 'download'])
    ->name('photo.download')
    ->middleware('auth');

Route::get('/fotografos', [PhotographerController::class, 'index'])->name('photographers.index');
Route::get('/fotografos/{slug}', [PhotographerController::class, 'show'])->name('photographers.show');

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
            'total_events' => \App\Models\Event::where('photographer_id', $photographer->id)->count(),
            'total_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->count(),
            'active_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->where('is_active', true)->count(),
            'total_downloads' => \App\Models\Photo::where('photographer_id', $photographer->id)->sum('downloads'),
        ];

        return Inertia::render('Photographer/Dashboard', [
            'stats' => $stats,
            'photographer' => $photographer,  // ← AGREGAR ESTA LÍNEA
        ]);
    })->name('dashboard');


    // Perfil
    Route::get('/mi-perfil', [App\Http\Controllers\Photographer\PhotographerProfileController::class, 'show'])->name('profile');
    Route::get('/mi-perfil/editar', [App\Http\Controllers\Photographer\PhotographerProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/mi-perfil/actualizar', [App\Http\Controllers\Photographer\PhotographerProfileController::class, 'update'])->name('profile.update');

    // Fotos
    Route::get('/fotos', [App\Http\Controllers\Photographer\PhotoController::class, 'index'])->name('photos.index');
    Route::get('/fotos/crear', [App\Http\Controllers\Photographer\PhotoController::class, 'create'])->name('photos.create');
    Route::post('/fotos', [App\Http\Controllers\Photographer\PhotoController::class, 'store'])->name('photos.store');
    Route::get('/fotos/{photo}', [App\Http\Controllers\Photographer\PhotoController::class, 'show'])->name('photos.show');
    Route::get('/fotos/{photo}/editar', [App\Http\Controllers\Photographer\PhotoController::class, 'edit'])->name('photos.edit');  // ← AGREGAR ESTA LÍNEA
    Route::put('/fotos/{photo}', [App\Http\Controllers\Photographer\PhotoController::class, 'update'])->name('photos.update');
    Route::delete('/fotos/{photo}', [App\Http\Controllers\Photographer\PhotoController::class, 'destroy'])->name('photos.destroy');

    // Eventos
    Route::get('/eventos', [App\Http\Controllers\Photographer\EventController::class, 'index'])->name('events.index');
    Route::get('/eventos/crear', [App\Http\Controllers\Photographer\EventController::class, 'create'])->name('events.create');
    Route::post('/eventos', [App\Http\Controllers\Photographer\EventController::class, 'store'])->name('events.store');
    Route::get('/eventos/{event}', [App\Http\Controllers\Photographer\EventController::class, 'show'])->name('events.show');
    Route::get('/eventos/{event}/editar', [App\Http\Controllers\Photographer\EventController::class, 'edit'])->name('events.edit');
    Route::put('/eventos/{event}', [App\Http\Controllers\Photographer\EventController::class, 'update'])->name('events.update');
    Route::delete('/eventos/{event}', [App\Http\Controllers\Photographer\EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/eventos/{event}/cover-image', [App\Http\Controllers\Photographer\EventController::class, 'updateCoverImage'])->name('events.cover-image');
});


require __DIR__ . '/auth.php';
