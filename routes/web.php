<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\Photographer\PhotoManagementController;
use App\Http\Controllers\Photographer\EventManagementController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// ====================================
// RUTAS PÚBLICAS (SIN AUTENTICACIÓN)
// ====================================

// Página principal
Route::get('/', [PublicGalleryController::class, 'index'])->name('home');

// Galería pública
Route::prefix('galeria')->name('gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'index'])->name('index');
    Route::get('/buscar', [PublicGalleryController::class, 'search'])->name('search');
    Route::get('/{uniqueId}', [PublicGalleryController::class, 'show'])->name('show');
});

// Eventos públicos
Route::prefix('eventos')->name('events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/{slug}', [EventController::class, 'show'])->name('show');
});

// Evento privado (con token)
Route::get('/evento-privado/{token}', [EventController::class, 'showPrivate'])->name('events.private');

// ====================================
// RUTAS DE AUTENTICACIÓN
// ====================================

Route::middleware('auth')->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ====================================
// RUTAS DE FOTÓGRAFO
// ====================================

Route::middleware(['auth', 'photographer'])
    ->prefix('fotografo')
    ->name('photographer.')
    ->group(function () {
        
        // Dashboard principal
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
            
            return Inertia::render('Photographer/Dashboard', [
                'photographer' => $photographer,
                'stats' => $stats,
            ]);
        })->name('dashboard');

        // Gestión de fotos
        Route::prefix('fotos')->name('photos.')->group(function () {
            Route::get('/', [PhotoManagementController::class, 'index'])->name('index');
            Route::get('/subir', [PhotoManagementController::class, 'create'])->name('create');
            Route::post('/', [PhotoManagementController::class, 'store'])->name('store');
            Route::get('/{id}/editar', [PhotoManagementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PhotoManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [PhotoManagementController::class, 'destroy'])->name('destroy');
        });

        // Gestión de eventos
        Route::prefix('eventos')->name('events.')->group(function () {
            Route::get('/', [EventManagementController::class, 'index'])->name('index');
            Route::get('/crear', [EventManagementController::class, 'create'])->name('create');
            Route::post('/', [EventManagementController::class, 'store'])->name('store');
            Route::get('/{id}', [EventManagementController::class, 'show'])->name('show');
            Route::get('/{id}/editar', [EventManagementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [EventManagementController::class, 'update'])->name('update');
            Route::delete('/{id}', [EventManagementController::class, 'destroy'])->name('destroy');
            
            // Gestión de fotos en eventos
            Route::post('/{id}/agregar-foto', [EventManagementController::class, 'addPhoto'])->name('addPhoto');
            Route::delete('/{id}/remover-foto', [EventManagementController::class, 'removePhoto'])->name('removePhoto');
            Route::post('/{id}/reordenar-fotos', [EventManagementController::class, 'reorderPhotos'])->name('reorderPhotos');
        });
    });

require __DIR__.'/auth.php';
