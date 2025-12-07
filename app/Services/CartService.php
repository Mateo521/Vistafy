<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;

class CartService
{
    /**
     * Obtener o crear carrito del usuario actual
     */
    public function getOrCreateCart()
    {
        if (!Auth::check()) {
            return null;
        }

        return Cart::firstOrCreate(
            ['user_id' => Auth::id()]
        );
    }

    /**
     * Agregar foto al carrito
     */
    public function addPhoto(Photo $photo)
    {
        $cart = $this->getOrCreateCart();

        if (!$cart) {
            throw new \Exception('Debes iniciar sesión para usar el carrito');
        }

        // Verificar si ya está en el carrito
        $exists = CartItem::where('cart_id', $cart->id)
            ->where('photo_id', $photo->id)
            ->exists();

        if ($exists) {
            return ['success' => false, 'message' => 'Esta foto ya está en tu carrito'];
        }

        CartItem::create([
            'cart_id' => $cart->id,
            'photo_id' => $photo->id,
            'price' => $photo->price,
        ]);

        return ['success' => true, 'message' => 'Foto agregada al carrito'];
    }

    /**
     * Eliminar foto del carrito
     */
    public function removePhoto($photoId)
    {
        $cart = $this->getOrCreateCart();

        if (!$cart) {
            return ['success' => false];
        }

        CartItem::where('cart_id', $cart->id)
            ->where('photo_id', $photoId)
            ->delete();

        return ['success' => true];
    }

    /**
     * Vaciar carrito
     */
    public function clear()
    {
        $cart = $this->getOrCreateCart();

        if ($cart) {
            $cart->items()->delete();
        }

        return ['success' => true];
    }

    /**
     * Obtener items del carrito con fotos
     */
    public function getItems()
    {
        $cart = $this->getOrCreateCart();

        if (!$cart) {
            return collect([]);
        }

        return $cart->items()
            ->with('photo.event', 'photo.photographer')
            ->get();
    }

    /**
     * Obtener total del carrito
     */
    public function getTotal()
    {
        $cart = $this->getOrCreateCart();
        return $cart ? $cart->total : 0;
    }

    /**
     * Contar items
     */
    public function getCount()
    {
        $cart = $this->getOrCreateCart();
        return $cart ? $cart->count : 0;
    }
}
