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
        if ($photographer->status === 'approved') {
            return back()->with('error', 'Este fotógrafo ya está aprobado.');
        }

        DB::beginTransaction();
        try {
            $photographer->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
                'is_active' => true,
                'is_verified' => true,
                'rejection_reason' => null,
            ]);

            DB::commit();

            // TODO: Enviar email de notificación al fotógrafo
            // Mail::to($photographer->user->email)->send(new PhotographerApprovedMail($photographer));

            return back()->with('success', "Fotógrafo '{$photographer->business_name}' aprobado correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
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
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        if ($photographer->status === 'suspended') {
            return back()->with('error', 'Este fotógrafo ya está suspendido.');
        }

        DB::beginTransaction();
        try {
            $photographer->update([
                'status' => 'suspended',
                'rejection_reason' => $request->reason,
                'is_active' => false,
            ]);

            DB::commit();

            // TODO: Enviar email de notificación al fotógrafo
            // Mail::to($photographer->user->email)->send(new PhotographerSuspendedMail($photographer));

            return back()->with('success', "Fotógrafo '{$photographer->business_name}' suspendido.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al suspender el fotógrafo: ' . $e->getMessage());
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
                'rejection_reason' => null,
                'approved_at' => $photographer->approved_at ?? now(),
                'approved_by' => $photographer->approved_by ?? auth()->id(),
            ]);

            DB::commit();

            // TODO: Enviar email de notificación al fotógrafo
            // Mail::to($photographer->user->email)->send(new PhotographerReactivatedMail($photographer));

            return back()->with('success', "Fotógrafo '{$photographer->business_name}' reactivado correctamente.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al reactivar el fotógrafo: ' . $e->getMessage());
        }
    }
}
