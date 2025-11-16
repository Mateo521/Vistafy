<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PhotographerProfileController extends Controller
{
    public function index()
    {
        return Inertia::render('Photographer/Profile/Index');
    }

    public function edit()
    {
        return Inertia::render('Photographer/Profile/Edit');
    }

    public function update(Request $request)
    {
        // Lógica para actualizar perfil
    }
}
