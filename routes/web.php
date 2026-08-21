<?php

use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\PhotographerManagementController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\PhotographerRegistrationController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\EventFaceSearchController;
use App\Http\Controllers\FutureEventController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentSimulationController;
use App\Http\Controllers\Photographer\EventController;
use App\Http\Controllers\Photographer\FutureEventManagementController;
use App\Http\Controllers\Photographer\MercadoPagoOAuthController;
use App\Http\Controllers\Photographer\PhotoController;
use App\Http\Controllers\Photographer\ProfileController as PhotographerProfileController;
use App\Http\Controllers\PhotographerController;
use App\Http\Controllers\PhotoViewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicGalleryController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PurchaseHistoryController;
use App\Http\Controllers\WebhookController;
use App\Models\ContactMessage;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

Route::get('/sitemap.xml', function () {
    $sitemap = Sitemap::create()
        ->add(Url::create('/')->setPriority(1.0)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
        ->add(Url::create('/nosotros')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
        ->add(Url::create('/contacto')->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_MONTHLY))
        ->add(Url::create('/fotografos')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY))
        ->add(Url::create('/eventos')->setPriority(0.9)->setChangeFrequency(Url::CHANGE_FREQUENCY_DAILY));

    \App\Models\Event::where('is_active', true)
        ->where('is_private', false)
        ->get()
        ->each(function ($event) use ($sitemap) {
            $sitemap->add(Url::create("/eventos/{$event->slug}")->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        });

    \App\Models\Photographer::where('status', 'approved')
        ->get()
        ->each(function ($photographer) use ($sitemap) {
            $sitemap->add(Url::create("/fotografos/{$photographer->slug}")->setPriority(0.8)->setChangeFrequency(Url::CHANGE_FREQUENCY_WEEKLY));
        });

    return $sitemap->toResponse(request());
});


Route::middleware('guest')->group(function () {
    Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback']);
});

Route::get('/terminos-y-condiciones', function () {
    return Inertia::render('Terms'); 
})->name('terms');
Route::get('/politica-de-privacidad', function () {
    return Inertia::render('Privacy');  
})->name('privacy');

Route::get(
    'foto/{photographer}/{year}/{month}/{day}/{type}/{filename}',
    [PhotoViewController::class, 'show']
)
    ->name('photo.view')
    ->where([
        'photographer' => '[0-9]+',
        'year' => '[0-9]{4}',
        'month' => '[0-9]{2}',
        'day' => '[0-9]{2}',
        'type' => 'watermarked|thumbnails',
        'filename' => '.*\.(jpg|jpeg|png|gif|webp|JPG|JPEG|PNG|GIF|WEBP)',
    ]);

Route::get('/', [PublicGalleryController::class, 'index'])->name('home');

Route::get('/contacto', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contacto', [ContactController::class, 'store'])->name('contact.store');

Route::get('/nosotros', function () {
    return Inertia::render('About');
})->name('about');

Route::prefix('eventos-futuros')->name('future-events.')->group(function () {

    Route::get('/api', [FutureEventController::class, 'index'])->name('api');

    Route::get('/mapa', function () {
        return Inertia::render('FutureEvents/Map');
    })->name('map');

    Route::get('/', [FutureEventController::class, 'page'])->name('index');

    Route::get('/{futureEvent}', [FutureEventController::class, 'show'])->name('show');
});

Route::prefix('galeria')->name('gallery.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'gallery'])->name('index');
    Route::get('/{uniqueId}', [PublicGalleryController::class, 'show'])->name('show');
    Route::post('/buscar', [PublicGalleryController::class, 'search'])->name('search');
    Route::post('/buscar-rostro', [PublicGalleryController::class, 'faceSearch'])
        ->name('face-search');

    Route::post('/buscar-dorsal', [PublicGalleryController::class, 'bibSearch'])
        ->name('bib-search');

    Route::get('/foto/{uniqueId}/disponibilidad', [PublicGalleryController::class, 'checkAvailability'])->name('check');
});

Route::prefix('eventos')->name('events.')->group(function () {
    Route::get('/', [PublicGalleryController::class, 'events'])->name('index');
    Route::get('/{event:slug}', [PublicGalleryController::class, 'showEvent'])->name('show');
    

    Route::get('/{event:slug}/fotografo/{photographer:slug}', [PublicGalleryController::class, 'showEventPhotographer'])->name('show-photographer');

    
    Route::get('/{event:slug}/buscar-rostro', [EventFaceSearchController::class, 'show'])
        ->name('face-search.show');

    Route::get('/{event:slug}/buscar-rostro', [EventFaceSearchController::class, 'index'])
        ->name('face-search');

    Route::post('/{event:slug}/buscar-rostro', [EventFaceSearchController::class, 'search'])
        ->name('face-search.submit');

    Route::get('/{event:slug}/buscar-dorsal', [EventController::class, 'bibSearch'])
        ->name('bib-search'); //

    Route::post('/{event:slug}/buscar-dorsal', [EventController::class, 'searchByBib'])
        ->name('search-bib'); //

});

Route::get('/fotografos', [PhotographerController::class, 'index'])->name('photographers.index');
Route::get('/fotografos/{slug}', [PhotographerController::class, 'show'])->name('photographers.show');

Route::get('/registro-fotografo', [PhotographerRegistrationController::class, 'create'])
    ->name('photographer.register');
Route::post('/registro-fotografo', [PhotographerRegistrationController::class, 'store']);

Route::middleware('auth')->prefix('pago')->name('payment.')->group(function () {

    // Iniciar compra
    Route::post('/fotos/{photo}/comprar', [PaymentController::class, 'initiatePurchase'])
        ->name('initiate');

    Route::post('/carrito/comprar', [PaymentController::class, 'initiateCartPurchase'])
        ->name('initiate.cart');

    if (app()->environment('local') && config('services.mercadopago.simulation_mode')) {
        Route::get('/simular/{purchase}', [PaymentSimulationController::class, 'show'])
            ->name('simulate');

        Route::post('/simular/{purchase}', [PaymentSimulationController::class, 'process'])
            ->name('simulate.process');
    }

    Route::get('/exito', [PaymentController::class, 'success'])->name('success');
    Route::get('/fallo', [PaymentController::class, 'failure'])->name('failure');
    Route::get('/pendiente', [PaymentController::class, 'pending'])->name('pending');
    Route::get('/orden/{purchase}/pagos', [PaymentController::class, 'splitCheckout'])->name('split.show');
    Route::get('/orden/{purchase}/pagos/status', [PaymentController::class, 'splitStatus'])->name('split.status');
    Route::post('/orden/{purchase}/pagos/{payment}/reintentar', [PaymentController::class, 'retrySplitPayment'])->name('split.retry');

    Route::get('/descargar/{token}', [PaymentController::class, 'download'])->name('download');
});

Route::get('/purchases/{purchase}/check-status', [PurchaseController::class, 'checkStatus'])
    ->name('purchases.check-status');

Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago']);

Route::get('/descargar/{uniqueId}', [PublicGalleryController::class, 'download'])
    ->name('photo.download')
    ->middleware('auth');

Route::middleware('auth')->prefix('mis-compras')->name('purchases.')->group(function () {
    Route::get('/', [PurchaseHistoryController::class, 'index'])->name('index');
    Route::get('/{purchase}/descargar/{photo}', [PurchaseHistoryController::class, 'download'])->name('download');
    /*
    Route::get('/{purchase}/descargar-todas', [PurchaseHistoryController::class, 'downloadAll'])->name('download.all');
    */
});

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

Route::middleware(['auth'])->prefix('fotografo')->name('photographer.')->group(function () {

    Route::get('/pendiente', function () {
        return Inertia::render('Photographer/Pending');
    })->name('pending');

    Route::get('/rechazado', function () {
        $photographer = auth()->user()->photographer;

        return Inertia::render('Photographer/Rejected', [
            'reason' => $photographer?->rejection_reason,
        ]);
    })->name('rejected');

    Route::get('/suspendido', function () {
        return Inertia::render('Photographer/Suspended');
    })->name('suspended');
});

Route::middleware(['auth', 'photographer.approved'])->prefix('fotografo')->name('photographer.')->group(function () {

    Route::post('/fotos/assign-to-event', [PhotoController::class, 'assignToEvent'])
        ->name('photos.assign-to-event');

    Route::prefix('perfil')->name('profile.')->group(function () {

        Route::get('/editar', [PhotographerProfileController::class, 'edit'])->name('edit');

        Route::patch('/actualizar', [PhotographerProfileController::class, 'update'])->name('update');

        Route::delete('/foto-perfil', [PhotographerProfileController::class, 'deleteProfilePhoto'])->name('photo.delete');
        Route::delete('/banner', [PhotographerProfileController::class, 'deleteBannerPhoto'])->name('banner.delete');
    });

    Route::prefix('mercadopago')->name('mercadopago.')->group(function () {

        Route::get('/vincular', [MercadoPagoOAuthController::class, 'redirectToProvider'])->name('auth');

        Route::get('/callback', [MercadoPagoOAuthController::class, 'handleProviderCallback'])->name('callback');

        Route::get('/desvincular', [MercadoPagoOAuthController::class, 'unlinkAccount'])->name('unlink');
    });

    Route::prefix('oportunidades')->name('opportunities.')->group(function () {
        Route::get('/', [FutureEventManagementController::class, 'index'])->name('index');
        Route::get('/crear', [FutureEventManagementController::class, 'create'])->name('create');
        Route::post('/', [FutureEventManagementController::class, 'store'])->name('store');
        Route::get('/{id}/editar', [FutureEventManagementController::class, 'edit'])->name('edit');
        Route::post('/{id}', [FutureEventManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [FutureEventManagementController::class, 'destroy'])->name('destroy');
    });

    Route::get('/panel', function () {
        $photographer = auth()->user()->photographer;

        $pendingInvitations = $photographer->guestEvents()
            ->wherePivotIn('status', ['invited', 'pending'])
            ->with('photographer:id,business_name') 
            ->get();

        $collaboratingEvents = $photographer->guestEvents()
            ->wherePivot('status', 'approved')
            ->with('photographer:id,business_name') 
            ->latest()
            ->take(4) 
            ->get();

        $receivedApplications = \App\Models\FutureEvent::where('photographer_id', $photographer->id)
            ->whereHas('collaborators', function ($query) {
                $query->where('future_event_photographer.status', 'requested');
            })
            ->with(['collaborators' => function ($query) {
                $query->where('future_event_photographer.status', 'requested');
            }])
            ->get()
            ->flatMap(function ($event) {
                return $event->collaborators->map(function ($applicant) use ($event) {
                    return [
                        'event_id' => $event->id,
                        'event_title' => $event->title,
                        'applicant_id' => $applicant->id,
                        'applicant_name' => $applicant->business_name,
                    ];
                });
            });

        $stats = [
            'total_events' => \App\Models\Event::where('photographer_id', $photographer->id)->count(),
            'total_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->count(),
            'active_photos' => \App\Models\Photo::where('photographer_id', $photographer->id)->where('is_active', true)->count(),
            'total_downloads' => \App\Models\Photo::where('photographer_id', $photographer->id)->sum('downloads'),
        ];

        $recentEvents = \App\Models\Event::where('photographer_id', $photographer->id)
            ->withCount('photos')->latest()->take(6)->get();

        $recentPhotos = \App\Models\Photo::where('photographer_id', $photographer->id)
            ->with('event:id,name')->latest()->take(8)->get();

        return Inertia::render('Photographer/Dashboard', [
            'stats' => $stats,
            'photographer' => $photographer,
            'recentEvents' => $recentEvents,
            'recentPhotos' => $recentPhotos,
            'pendingInvitations' => $pendingInvitations,
            'collaboratingEvents' => $collaboratingEvents,
            'receivedApplications' => $receivedApplications,  
        ]);
    })->name('dashboard');

    Route::get('/perfil/editar', [App\Http\Controllers\Photographer\ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/perfil/actualizar', [App\Http\Controllers\Photographer\ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil/foto-perfil', [App\Http\Controllers\Photographer\ProfileController::class, 'deleteProfilePhoto'])->name('profile.photo.delete');
    Route::delete('/perfil/banner', [App\Http\Controllers\Photographer\ProfileController::class, 'deleteBannerPhoto'])->name('profile.banner.delete');

    Route::get('/fotos', [PhotoController::class, 'index'])->name('photos.index');
    Route::get('/fotos/crear', [PhotoController::class, 'create'])->name('photos.create');
    Route::post('/fotos', [PhotoController::class, 'store'])->name('photos.store');
    Route::get('/fotos/{photo}', [PhotoController::class, 'show'])->name('photos.show');
    Route::get('/fotos/{photo}/editar', [PhotoController::class, 'edit'])->name('photos.edit');
    Route::put('/fotos/{photo}', [PhotoController::class, 'update'])->name('photos.update');
    Route::delete('/fotos/{photo}', [PhotoController::class, 'destroy'])->name('photos.destroy');

    Route::get('/eventos', [EventController::class, 'index'])->name('events.index');
    Route::get('/eventos/crear', [EventController::class, 'create'])->name('events.create');
    Route::post('/eventos', [EventController::class, 'store'])->name('events.store');
    Route::get('/eventos/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('/eventos/{event}/editar', [EventController::class, 'edit'])->name('events.edit');
    Route::put('/eventos/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('/eventos/{event}', [EventController::class, 'destroy'])->name('events.destroy');
    Route::post('/eventos/{event}/cover-image', [EventController::class, 'updateCoverImage'])->name('events.cover-image');
    Route::post('/eventos/{event}/invitar', [EventController::class, 'inviteColleague'])->name('events.invite');


    Route::post('/oportunidades/{event}/aceptar', [FutureEventManagementController::class, 'acceptInvitation'])->name('opportunities.accept');
    Route::post('/oportunidades/{event}/rechazar', [FutureEventManagementController::class, 'rejectInvitation'])->name('opportunities.reject');
    Route::post('/eventos-futuros/{event}/postular', [\App\Http\Controllers\FutureEventController::class, 'apply'])->name('future-events.apply');

    Route::post('/eventos-futuros/{futureEvent}/postulantes/{photographer}/aceptar', [\App\Http\Controllers\FutureEventController::class, 'acceptApplication'])->name('future-events.applications.accept');
    Route::post('/eventos-futuros/{futureEvent}/postulantes/{photographer}/rechazar', [\App\Http\Controllers\FutureEventController::class, 'rejectApplication'])->name('future-events.applications.reject');


});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

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
            'unread_messages' => ContactMessage::unread()->count(),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    })->name('dashboard');

    Route::get('/mensajes', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/mensajes/{message}', [ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('/mensajes/{message}/toggle-read', [ContactMessageController::class, 'toggleRead'])->name('messages.toggle-read');
    Route::delete('/mensajes/{message}', [ContactMessageController::class, 'destroy'])->name('messages.destroy');

    Route::prefix('fotografos')->name('photographers.')->group(function () {
        Route::get('/', [PhotographerManagementController::class, 'index'])->name('index');

        Route::get('/{photographer}', [PhotographerManagementController::class, 'show'])->name('show');
        Route::post('/{photographer}/aprobar', [PhotographerManagementController::class, 'approve'])->name('approve');
        Route::post('/{photographer}/rechazar', [PhotographerManagementController::class, 'reject'])->name('reject');
        Route::post('/{photographer}/suspender', [PhotographerManagementController::class, 'suspend'])->name('suspend');
        Route::post('/{photographer}/reactivar', [PhotographerManagementController::class, 'reactivate'])->name('reactivate');
        Route::post('/{photographer}/revertir', [PhotographerManagementController::class, 'revert'])->name('revert');
    });

});

Route::fallback(function () {
    return Inertia::render('Errors/404');
});

require __DIR__.'/auth.php';
