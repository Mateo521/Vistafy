<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PhotoReport;
use Inertia\Inertia;

class PhotoReportController extends Controller
{
    public function index()
    {

        $reports = PhotoReport::with(['photo.photographer.user'])
            ->where('status', 'pending')
            ->latest()
            ->paginate(15);

        return Inertia::render('Admin/Reports/Index', [
            'reports' => $reports
        ]);
    }

    public function accept(PhotoReport $report)
    {

        $report->photo->update(['is_active' => false]);


        $report->update(['status' => 'accepted']);



        return back()->with('success', 'Reporte aceptado. La fotografía fue eliminada del sitio público.');
    }

    public function reject(PhotoReport $report)
    {

        $report->update(['status' => 'rejected']);

        return back()->with('success', 'Reporte descartado. La fotografía sigue pública.');
    }
}