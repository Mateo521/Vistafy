<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
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
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:20',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.required' => 'Por favor ingresa tu nombre',
            'email.required' => 'Por favor ingresa tu correo',
            'email.email' => 'Ingresa un correo válido',
            'subject.required' => 'Por favor ingresa un asunto',
            'message.required' => 'Por favor escribe tu mensaje',
            'message.min' => 'El mensaje debe tener al menos 10 caracteres',
        ]);

        ContactMessage::create($validated);

        return back()->with('success', '¡Mensaje enviado correctamente! Te contactaremos pronto.');
    }
}
