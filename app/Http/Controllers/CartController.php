<?php

namespace App\Http\Controllers;

use App\Models\Photo;
use App\Services\CartService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CartController extends Controller
{
    protected $cartService;

    public function __construct(CartService $cartService)
    {
        // ❌ ELIMINAR: $this->middleware('auth');
        $this->cartService = $cartService;
    }

    /**
     * Mostrar carrito
     */
    public function index()
    {
        $items = $this->cartService->getItems();
        $total = $this->cartService->getTotal();

        return Inertia::render('Cart/Index', [
            'items' => $items,
            'total' => $total,
        ]);
    }

    /**
     * Agregar foto al carrito
     */
    public function add(Request $request, Photo $photo)
    {
        try {
            $result = $this->cartService->addPhoto($photo);
            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Eliminar foto del carrito
     */
    public function remove(Request $request, $photoId)
    {
        $result = $this->cartService->removePhoto($photoId);
        return response()->json($result);
    }

    /**
     * Vaciar carrito
     */
    public function clear()
    {
        $result = $this->cartService->clear();
        return response()->json($result);
    }

    /**
     * Obtener count para el header
     */
    public function count()
    {
        return response()->json([
            'count' => $this->cartService->getCount(),
        ]);
    }
}
