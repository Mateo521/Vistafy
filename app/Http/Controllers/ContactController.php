<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Rules\Turnstile; 
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContactController extends Controller
{
    public function index()
    {
        return Inertia::render('Contact/Index');
    }

    public function store(Request $request)
    {
        $validated =$request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',

            'turnstile_response' => ['required', new Turnstile()], 
        ], [

            'turnstile_response.required' => 'Por favor, completá la verificación de seguridad.',
        ]);

        unset($validated['turnstile_response']);

        ContactMessage::create($validated);

        return back()->with('success', '¡Mensaje enviado correctamente! Te contactaremos pronto.');
    }
}