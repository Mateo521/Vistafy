<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Photo;
use Illuminate\Support\Facades\Auth;

class CartService
{
    public function getOrCreateCart()
    {
        if (! Auth::check()) {
            return null;
        }

        return Cart::firstOrCreate(
            ['user_id' => Auth::id()]
        );
    }

    public function addPhoto(Photo $photo)
    {
        if (Auth::check()) {

            $cart = $this->getOrCreateCart();

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

        } else {

            $cart = session()->get('guest_cart', []);

            if (in_array($photo->id, $cart)) {
                return ['success' => false, 'message' => 'Esta foto ya está en tu carrito'];
            }

            $cart[] = $photo->id;
            session()->put('guest_cart', $cart);
        }

        return ['success' => true, 'message' => 'Foto agregada al carrito'];
    }

    public function removePhoto($photoId)
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateCart();
            if ($cart) {
                CartItem::where('cart_id', $cart->id)
                    ->where('photo_id', $photoId)
                    ->delete();
            }
        } else {
            $cart = session()->get('guest_cart', []);
            // Filtrar y reindexar el arreglo
            $cart = array_values(array_filter($cart, function ($id) use ($photoId) {
                return $id != $photoId;
            }));
            session()->put('guest_cart', $cart);
        }

        return ['success' => true];
    }

    public function clear()
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateCart();
            if ($cart) {
                $cart->items()->delete();
            }
        } else {
            session()->forget('guest_cart');
        }

        return ['success' => true];
    }

    public function getItems()
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateCart();

            return $cart ? $cart->items()->with('photo.event', 'photo.photographer')->get() : collect([]);
        } else {
            $ids = session()->get('guest_cart', []);
            if (empty($ids)) {
                return collect([]);
            }

            $photos = Photo::with('event', 'photographer')->whereIn('id', $ids)->get();

            return $photos->map(function ($photo) {
                return (object) [
                    'id' => 'guest_'.$photo->id,
                    'photo_id' => $photo->id,
                    'price' => $photo->price,
                    'photo' => $photo,
                ];
            });
        }
    }

    public function getTotal()
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateCart();

            return $cart ? $cart->total : 0;
        } else {
            $ids = session()->get('guest_cart', []);
            if (empty($ids)) {
                return 0;
            }

            return Photo::whereIn('id', $ids)->sum('price');
        }
    }

    public function getCount()
    {
        if (Auth::check()) {
            $cart = $this->getOrCreateCart();

            return $cart ? $cart->count : 0;
        } else {
            return count(session()->get('guest_cart', []));
        }
    }
}
