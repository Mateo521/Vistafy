<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Photographer;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class PhotographerManagementController extends Controller
{
    /**
     * Mostrar lista de fotógrafos con filtros
     */
    public function index(Request $request)
    {
        $status = $request->input('status', 'all');
        $search = $request->input('search', '');

        $query = Photographer::with(['user', 'events', 'photos'])
            ->withCount(['events', 'photos'])
            ->when($status !== 'all', function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->when($search, function ($q) use ($search) {
                return $q->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('business_name', 'like', "%{$search}%");
            })
            ->latest();

        $photographers = $query->paginate(15)->withQueryString();

        // Estadísticas
        $stats = [
            'total' => Photographer::count(),
            'pending' => Photographer::where('status', 'pending')->count(),
            'approved' => Photographer::where('status', 'approved')->count(),
            'rejected' => Photographer::where('status', 'rejected')->count(),
            'suspended' => Photographer::where('status', 'suspended')->count(),
        ];

        return Inertia::render('Admin/Photographers/Index', [
            'photographers' => $photographers,
            'stats' => $stats,
            'filters' => [
                'status' => $status,
                'search' => $search,
            ],
        ]);
    }

    /**
     *  NUEVO: Mostrar detalles de un fotógrafo
     */
    public function show(Photographer $photographer)
    {
        $photographer->load(['user', 'events', 'photos']);

        // Estadísticas del fotógrafo
        $stats = [
            'events' => $photographer->events()->count(),
            'photos' => $photographer->photos()->count(),
            'downloads' => $photographer->photos()->sum('downloads') ?? 0,
        ];

        return Inertia::render('Admin/Photographers/Show', [
            'photographer' => $photographer,
            'stats' => $stats,
        ]);
    }

    /**
     * Aprobar un fotógrafo
     */
  public function approve(Photographer $photographer)
{
    //  Log 1: Verificar que llega al método
    \Log::info(' APPROVE: Método llamado', [
        'photographer_id' => $photographer->id,
        'photographer_slug' => $photographer->slug,
        'status_actual' => $photographer->status,
        'user_id' => auth()->id(),
    ]);

    if ($photographer->status === 'approved') {
        \Log::info(' APPROVE: Ya está aprobado');
        return back()->with('error', 'Este fotógrafo ya está aprobado.');
    }

    DB::beginTransaction();
    try {
        \Log::info(' APPROVE: Antes de actualizar', [
            'data' => [
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'is_active' => true,
                'is_verified' => true,
            ]
        ]);

        $photographer->update([
            'status' => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'is_active' => true,
            'is_verified' => true,
            'rejection_reason' => null,
        ]);

        \Log::info(' APPROVE: Después de actualizar', [
            'status_nuevo' => $photographer->fresh()->status,
            'is_active' => $photographer->fresh()->is_active,
            'is_verified' => $photographer->fresh()->is_verified,
        ]);

        DB::commit();
        
        \Log::info(' APPROVE: Commit exitoso');

        return back()->with('success', "Fotógrafo '{$photographer->business_name}' aprobado correctamente.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error(' APPROVE: Error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return back()->with('error', 'Error al aprobar el fotógrafo: ' . $e->getMessage());
    }
}

    /**
     * Rechazar un fotógrafo
     */
    public function reject(Request $request, Photographer $photographer)
    {
        $request->validate([
            'reason' => 'required|string|min:10|max:500',
        ], [
            'reason.required' => 'Debes proporcionar un motivo del rechazo.',
            'reason.min' => 'El motivo debe tener al menos 10 caracteres.',
            'reason.max' => 'El motivo no puede exceder 500 caracteres.',
        ]);

        if ($photographer->status === 'rejected') {
            return back()->with('error', 'Este fotógrafo ya está rechazado.');
        }

        DB::beginTransaction();
        try {
            $photographer->update([
                'status' => 'rejected',
                'rejection_reason' => $request->reason,
                'is_active' => false,
                'is_verified' => false,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            DB::commit();

            // TODO: Enviar email de notificación al fotógrafo
            // Mail::to($photographer->user->email)->send(new PhotographerRejectedMail($photographer));

            return back()->with('success', "Fotógrafo '{$photographer->business_name}' rechazado.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al rechazar el fotógrafo: ' . $e->getMessage());
        }
    }

    /**
     *  NUEVO: Revertir rechazo (volver a pendiente)
     */
    public function revert(Photographer $photographer)
    {
        if ($photographer->status !== 'rejected') {
            return back()->with('error', 'Solo se pueden revertir fotógrafos rechazados.');
        }

        DB::beginTransaction();
        try {
            $photographer->update([
                'status' => 'pending',
                'rejection_reason' => null,
                'is_active' => false,
                'is_verified' => false,
                'approved_at' => null,
                'approved_by' => null,
            ]);

            DB::commit();

            // TODO: Enviar email notificando la reversión
            // Mail::to($photographer->user->email)->send(new PhotographerRevertedMail($photographer));

            return back()->with('success', "Fotógrafo '{$photographer->business_name}' devuelto a estado Pendiente para revisión.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al revertir el rechazo: ' . $e->getMessage());
        }
    }

/**
 * Suspender un fotógrafo
 */
public function suspend(Request $request, Photographer $photographer)
{
    \Log::info('🔵 SUSPEND: Método llamado', [
        'photographer_id' => $photographer->id,
        'request_data' => $request->all(),
    ]);

    $request->validate([
        'reason' => 'nullable|string|max:500',  //  Cambiar a nullable
    ]);

    if ($photographer->status === 'suspended') {
        return back()->with('error', 'Este fotógrafo ya está suspendido.');
    }

    DB::beginTransaction();
    try {
        $photographer->update([
            'status' => 'suspended',
            'suspended_at' => now(),
            'suspended_by' => auth()->id(),
            'suspension_reason' => $request->reason,  //  Puede ser null
            'is_active' => false,
        ]);

        DB::commit();
        
        \Log::info(' SUSPEND: Fotógrafo suspendido');

        return back()->with('success', "Fotógrafo '{$photographer->business_name}' suspendido.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error(' SUSPEND: Error', ['error' => $e->getMessage()]);
        return back()->with('error', 'Error al suspender: ' . $e->getMessage());
    }
}

/**
 * Reactivar un fotógrafo suspendido
 */
public function reactivate(Photographer $photographer)
{
    if ($photographer->status !== 'suspended') {
        return back()->with('error', 'Solo se pueden reactivar fotógrafos suspendidos.');
    }

    DB::beginTransaction();
    try {
        $photographer->update([
            'status' => 'approved',
            'is_active' => true,
            'is_verified' => true,
            'suspension_reason' => null,               //  Limpiar suspension_reason
            'suspended_at' => null,                    //  Limpiar suspended_at
            'suspended_by' => null,                    //  Limpiar suspended_by
            'approved_at' => $photographer->approved_at ?? now(),
            'approved_by' => $photographer->approved_by ?? auth()->id(),
        ]);

        DB::commit();

        \Log::info(' REACTIVATE: Fotógrafo reactivado', [
            'photographer_id' => $photographer->id,
        ]);

        return back()->with('success', "Fotógrafo '{$photographer->business_name}' reactivado correctamente.");
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error(' REACTIVATE: Error', ['error' => $e->getMessage()]);
        return back()->with('error', 'Error al reactivar el fotógrafo: ' . $e->getMessage());
    }
}

}
