<?php

namespace App\Policies;

use App\Models\Photo;
use App\Models\User;

class PhotoPolicy
{
    /**
     * Ver foto
     */
    public function view(User $user, Photo$photo)
    {
        return $user->photographer && $user->photographer->id === $photo->photographer_id;
    }

    /**
     * Actualizar foto
     */
    public function update(User $user, Photo$photo)
    {
        return $user->photographer && $user->photographer->id === $photo->photographer_id;
    }

    /**
     * Eliminar foto
     */
    public function delete(User $user, Photo$photo)
    {
        return $user->photographer && $user->photographer->id === $photo->photographer_id;
    }
}
